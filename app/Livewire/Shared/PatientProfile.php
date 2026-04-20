<?php

namespace App\Livewire\Shared;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\Visit;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientProfile extends Component
{
    use WithFileUploads;
    use \App\Traits\HasMedicalSuggestions;

    public Patient $patient;
    
    // File Upload properties
    public $newFile;
    public $fileType = 'lab';
    
    // UI Toggles & Edits
    public $isEditingFH = false;
    public $isEditingPH = false;
    public $familyHistoryEdit;
    public $personalHistoryEdit;
    
    // Edit Patient properties
    public $showEditModal = false;
    public $editName, $editPhone, $editAgeYears, $editAgeMonths, $editAgeDays, $editAddress, $editWeight;

    // Visit Recording properties
    public $showVisitModal = false;
    public $complaint = '';
    public $diagnosis = '';
    public $investigation = '';
    public $treatmentText = '';
    public $treatmentFile = null;
    public $parentVisitId = null;
    public $visitType = 'checkup';
    public $followUpNotes = '';
    public $specialtyFields = [];
    public $dynamicAnswers = [];

    // Booking properties
    public $showBookingModal = false;
    public $bookingDoctorId = '';
    public $bookingDate = '';
    public $bookingTime = '09:00';
    public $bookingType = 'checkup';


    public function updatedComplaint($value)
    {
        $this->complaintSuggestions = $this->getMedicalSuggestions('complaint', $value, 'complaints');
    }

    public function updatedDiagnosis($value)
    {
        $this->diagnosisSuggestions = $this->getMedicalSuggestions('diagnosis', $value, 'diagnosis');
    }

    public function updatedInvestigation($value)
    {
        $this->investigationSuggestions = $this->getMedicalSuggestions('investigation', $value, 'investigations');
    }

    public function updatedTreatmentText($value)
    {
        $this->treatmentSuggestions = $this->getMedicalSuggestions('treatmentText', $value, 'treatments');
    }

    public function updatedFamilyHistoryEdit($value)
    {
        $this->familyHistorySuggestions = $this->getMedicalSuggestions('familyHistoryEdit', $value, 'history');
    }

    public function updatedPersonalHistoryEdit($value)
    {
        $this->personalHistorySuggestions = $this->getMedicalSuggestions('personalHistoryEdit', $value, 'history');
    }

    public function selectComplaint($value)
    {
        $this->complaint = $value;
        $this->complaintSuggestions = [];
    }

    public function selectDiagnosis($value)
    {
        $this->diagnosis = $value;
        $this->diagnosisSuggestions = [];
    }

    public function selectSuggestionFor($field, $value)
    {
        $currentText = $this->$field;
        
        $lastComma = mb_strrpos($currentText, ',');
        $lastNewline = mb_strrpos($currentText, "\n");
        $lastArabicComma = mb_strrpos($currentText, '،');
        
        $lastSeparatorPoint = max(
            $lastComma !== false ? $lastComma : -1, 
            $lastNewline !== false ? $lastNewline : -1,
            $lastArabicComma !== false ? $lastArabicComma : -1
        );
        
        if ($lastSeparatorPoint !== -1) {
            // Keep everything up to the separator, and add the new value
            $prefix = mb_substr($currentText, 0, $lastSeparatorPoint + 1);
            
            // Add a space if it's a comma (for better formatting)
            $space = mb_substr($prefix, -1) === "\n" ? "" : " ";
            $this->$field = $prefix . $space . $value;
        } else {
            $this->$field = $value;
        }

        $suggestionField = $field . 'Suggestions';
        if ($field === 'treatmentText') $suggestionField = 'treatmentSuggestions';
        
        $this->$suggestionField = [];
    }


    public function openVisitModal()
    {
        $this->resetVisitForm();
        
        // Load Specialty Fields
        $doctor = auth()->user()->isDoctor() ? auth()->user() : \App\Models\User::find(auth()->user()->doctor_id);
        if ($doctor && $doctor->specialty) {
            $this->specialtyFields = $doctor->specialty->fields;
            foreach ($this->specialtyFields as $field) {
                $this->dynamicAnswers[$field->id] = $field->type === 'multi_select' ? [] : '';
            }
        }
        
        $this->showVisitModal = true;
    }

    public function closeVisitModal()
    {
        $this->showVisitModal = false;
        $this->resetVisitForm();
    }

    public function startFollowUp($visitId)
    {
        $this->resetVisitForm();
        $this->showVisitModal = true;
        $this->parentVisitId = $visitId;
        $this->visitType = 'follow_up';
        
        $parentVisit = Visit::find($visitId);
        if ($parentVisit) {
            $this->complaint = $parentVisit->complaint;
            $this->diagnosis = $parentVisit->diagnosis;
        }
    }

    public function saveVisit()
    {
        $rules = [
            'complaint' => 'nullable|string|max:5000',
            'diagnosis' => 'nullable|string|max:2000',
            'investigation' => 'nullable|string|max:5000',
            'treatmentText' => 'nullable|string|max:5000',
            'treatmentFile' => 'nullable|file|max:10240',
            'followUpNotes' => 'nullable|string|max:5000',
        ];

        foreach ($this->specialtyFields as $field) {
            $rules['dynamicAnswers.' . $field->id] = 'nullable';
        }

        $this->validate($rules, [], [
            'dynamicAnswers.*' => __('Specialty Field'),
        ]);

        /** @var \App\Models\User $doctor */
        $doctor = auth()->user()->isDoctor() ? auth()->user() : auth()->user()->assignedDoctor;

        if ($this->treatmentFile) {
            $fileSize = $this->treatmentFile->getSize();
            if ($doctor && !$doctor->hasStorageSpace($fileSize)) {
                $this->addError('treatmentFile', __('Storage limit reached for this clinic. Please contact administration.'));
                return;
            }
        }

        $treatmentFilePath = null;
        if ($this->treatmentFile) {
            $treatmentFilePath = $this->treatmentFile->store('treatments', 'public');
            \App\Services\FileService::optimizeImageInPlace('public', $treatmentFilePath);
            \App\Services\FileService::compressFileInPlace('public', $treatmentFilePath);
            
            // Increment storage quota with final size
            if ($doctor) {
                $finalSize = \Illuminate\Support\Facades\Storage::disk('public')->size($treatmentFilePath);
                $doctor->increment('used_storage_bytes', $finalSize);
            }
        }

        $doctorId = auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id;

        $this->patient->visits()->create([
            'doctor_id' => $doctorId,
            'complaint' => $this->complaint,
            'diagnosis' => $this->diagnosis,
            'history' => $this->investigation,
            'treatment_text' => $this->treatmentText,
            'treatment_file_path' => $treatmentFilePath,
            'follow_up_notes' => $this->followUpNotes,
            'parent_visit_id' => $this->parentVisitId,
            'type' => $this->visitType,
            'specialty_data' => $this->dynamicAnswers,
        ]);

        $this->closeVisitModal();

        $this->closeVisitModal();
        session()->flash('visit_message', __('Visit recorded successfully.'));
    }

    private function resetVisitForm()
    {
        $this->reset([
            'complaint', 'diagnosis', 'investigation', 'treatmentText', 
            'treatmentFile', 'parentVisitId', 'visitType', 'followUpNotes', 
            'dynamicAnswers'
        ]);
        $this->complaintSuggestions = [];
        $this->diagnosisSuggestions = [];
        $this->investigationSuggestions = [];
        $this->treatmentSuggestions = [];
    }

    public function saveFamilyHistory()
    {
        $this->validate(['familyHistoryEdit' => 'nullable|string|max:2000']);
        $this->patient->update(['family_history' => $this->familyHistoryEdit]);
        $this->dispatch('close-fh-edit'); // If using events for Alpine
        session()->flash('message', __('Family history updated.'));
    }

    public function savePersonalHistory()
    {
        $this->validate(['personalHistoryEdit' => 'nullable|string|max:2000']);
        $this->patient->update(['personal_history' => $this->personalHistoryEdit]);
        $this->dispatch('close-ph-edit');
        session()->flash('message', __('Personal history updated.'));
    }


    public function uploadFile()
    {
        if (!auth()->user()->isDoctor() && !auth()->user()->isSecretary()) return;

        /** @var \App\Models\User $doctor */
        $doctor = auth()->user()->isDoctor() ? auth()->user() : auth()->user()->assignedDoctor;

        $this->validate([
            'newFile' => 'required|file|max:20480',
            'fileType' => 'required|in:lab,investigation,other',
        ]);

        $fileSize = $this->newFile->getSize();
        
        // Check storage limit
        if ($doctor && !$doctor->hasStorageSpace($fileSize)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'newFile' => [__('Storage limit reached for this clinic. Please contact administration.')]
            ]);
        }

        $path = $this->newFile->store('patient_files', 'public');

        // Optimize or Compress in place
        $mime = $this->newFile->getMimeType();
        if (str_starts_with($mime, 'image/')) {
            \App\Services\FileService::optimizeImageInPlace('public', $path);
        } else {
            \App\Services\FileService::compressFileInPlace('public', $path);
        }

        // Recalculate size after compression for accurate used_storage_bytes
        $finalSize = Storage::disk('public')->size($path);

        $this->patient->files()->create([
            'file_name' => $this->newFile->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $this->fileType,
            'uploaded_by' => auth()->id(),
        ]);

        // Update used_storage_bytes with final (compressed) size
        if ($doctor) {
            $doctor->increment('used_storage_bytes', $finalSize);
        }

        $this->reset(['newFile', 'fileType']);
        $this->patient->load('files');
        session()->flash('file_message', __('Medical file uploaded successfully.'));
    }

    public function deleteFile($fileId)
    {
        if (!auth()->user()->isDoctor() && !auth()->user()->isSecretary()) return;

        $file = PatientFile::findOrFail($fileId);
        $fileSize = 0;

        if (Storage::disk('public')->exists($file->file_path)) {
            $fileSize = Storage::disk('public')->size($file->file_path);
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        /** @var \App\Models\User $doctor */
        $doctor = auth()->user()->isDoctor() ? auth()->user() : auth()->user()->assignedDoctor;
        
        if ($doctor && $fileSize > 0) {
            $doctor->decrement('used_storage_bytes', $fileSize);
        }

        $this->patient->load('files');
        session()->flash('file_message', __('File deleted successfully.'));
    }

    public function openBooking()
    {
        if (auth()->user()->isDoctor()) {
            $this->bookingDoctorId = auth()->id();
        } elseif (auth()->user()->isSecretary()) {
            $this->bookingDoctorId = auth()->user()->doctor_id;
        } else {
            $doctors = \App\Models\User::where('role', 'doctor')->get();
            $this->bookingDoctorId = $doctors->count() === 1 ? $doctors->first()->id : '';
        }
        
        $this->bookingDate = now()->format('Y-m-d');
        $this->bookingTime = now()->addMinutes(5)->format('H:i');
        $this->bookingType = 'checkup';
        $this->showBookingModal = true;
    }

    public function closeBookingModal() { $this->showBookingModal = false; }

    public function confirmBooking()
    {
        $this->validate([
            'bookingDoctorId' => 'required|exists:users,id',
            'bookingDate' => 'required|date',
            'bookingTime' => 'required',
            'bookingType' => 'required|in:checkup,follow_up',
        ]);

        app(\App\Services\AppointmentService::class)->bookAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->bookingDoctorId,
            'scheduled_at' => \Carbon\Carbon::parse($this->bookingDate . ' ' . $this->bookingTime),
            'status' => 'pending',
            'type' => $this->bookingType,
        ]);

        $this->closeBookingModal();
        session()->flash('message', __('Appointment booked successfully.'));
    }

    public function openEditModal()
    {
        $this->editName = $this->patient->name;
        $this->editPhone = $this->patient->phone;
        $this->editAgeYears = $this->patient->age_years;
        $this->editAgeMonths = $this->patient->age_months;
        $this->editAgeDays = $this->patient->age_days;
        $this->editWeight = $this->patient->weight;
        $this->editAddress = $this->patient->address;
        $this->showEditModal = true;
    }

    public function closeEditModal() { $this->showEditModal = false; }

    public function savePatientData()
    {
        $this->validate([
            'editName' => 'required|min:3',
            'editPhone' => 'nullable|numeric',
            'editAgeYears' => 'nullable|numeric',
            'editAgeMonths' => 'nullable|numeric',
            'editAgeDays' => 'nullable|numeric',
            'editWeight' => 'nullable|numeric',
            'editAddress' => 'nullable|string|max:500',
        ]);

        app(\App\Services\PatientService::class)->updatePatient($this->patient->id, [
            'name' => $this->editName,
            'phone' => $this->editPhone,
            'age_years' => $this->editAgeYears,
            'age_months' => $this->editAgeMonths,
            'age_days' => $this->editAgeDays,
            'weight' => $this->editWeight,
            'address' => $this->editAddress,
        ]);

        $this->patient->refresh();
        $this->closeEditModal();
        session()->flash('message', __('Patient data updated successfully.'));
    }

    public function deletePatient()
    {
        if (!auth()->user()->isDoctor() && !auth()->user()->isAdmin()) {
            return;
        }

        $this->patient->delete();
        
        session()->flash('success', __('Patient record deleted successfully.'));
        
        $redirectUrl = auth()->user()->isDoctor() ? route('doctor.dashboard') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('secretary.dashboard'));
        return redirect($redirectUrl);
    }

    public function render()
    {
        // Aggregate files
        $medicalFiles = $this->patient->files()->orderBy('created_at', 'desc')->get()->map(fn($f) => [
            'id' => $f->id,
            'name' => $f->file_name,
            'path' => $f->file_path,
            'type' => $f->file_type,
            'date' => $f->created_at,
            'source' => 'patient_file',
            'url' => route('files.patient', $f->id),
        ]);

        $visitFiles = $this->patient->visits()
            ->whereNotNull('treatment_file_path')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => __('Prescription/Attachment'),
                'path' => $v->treatment_file_path,
                'type' => 'prescription',
                'date' => $v->created_at,
                'source' => 'visit',
                'url' => route('files.visit', $v->id),
            ]);

        $allFiles = $medicalFiles->concat($visitFiles)->unique('path')->sortByDesc('date');

        return view('livewire.shared.patient-profile', [
            'allFiles' => $allFiles,
            'primaryVisits' => $this->patient->visits()
                ->whereNull('parent_visit_id')
                ->with(['doctor', 'followUps.doctor'])
                ->orderBy('created_at', 'desc')
                ->get(),
            'doctors' => auth()->user()->isDoctor() 
                ? \App\Models\User::where('id', auth()->id())->get() 
                : \App\Models\User::where('role', 'doctor')->get()
        ])->layout('layouts.clinic', ['title' => __('Patient Profile')]);
    }
}
