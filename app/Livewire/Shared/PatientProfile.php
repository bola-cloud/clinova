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
    public $uploads = [];
    public $patientFiles = [];
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


    public function updatedUploads()
    {
        $this->validate([
            'uploads.*' => 'nullable|file|max:2048', // 2MB max
        ], [
            'uploads.*.max' => __('The file size must not exceed 2MB.'),
        ]);

        foreach ($this->uploads as $file) {
            // Save to persistent temp directory immediately
            $tempPath = $file->store('temp_patient_files', 'public');
            
            $this->patientFiles[] = [
                'name' => $file->getClientOriginalName(),
                'type' => $file->getClientMimeType(),
                'temp_path' => $tempPath,
                'id' => uniqid() // For stable wire:key
            ];
        }

        $this->reset(['uploads']);
    }

    public function deletePatientFile($fileId)
    {
        foreach ($this->patientFiles as $index => $fileData) {
            if ($fileData['id'] === $fileId) {
                // Delete physical temp file
                if (isset($fileData['temp_path'])) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($fileData['temp_path']);
                }
                
                // Remove from state
                unset($this->patientFiles[$index]);
                break;
            }
        }
        $this->patientFiles = array_values($this->patientFiles);
    }

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
        $this->$field = $value;
        $suggestionField = $field . 'Suggestions';
        if ($field === 'history') $suggestionField = 'investigationSuggestions';
        if ($field === 'treatment_text') $suggestionField = 'treatmentSuggestions';
        
        $this->$suggestionField = [];
    }

    public function mount(Patient $patient)
    {
        $this->patient = $patient;
        $this->familyHistoryEdit = $patient->family_history;
        $this->personalHistoryEdit = $patient->personal_history;
        
        $user = auth()->user();
        if ($user->role === 'doctor') {
            $specialty = $user->specialty;
            if ($specialty) {
                $this->specialtyFields = $specialty->fields;
            }
        }
    }

    public function openVisitModal()
    {
        $this->resetVisitForm();
        $this->showVisitModal = true;
    }

    public function closeVisitModal()
    {
        $this->showVisitModal = false;
        // Clean up any uploaded temp files if modal closed without saving
        if ($this->patientFiles) {
            foreach ($this->patientFiles as $fileData) {
                if (isset($fileData['temp_path'])) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($fileData['temp_path']);
                }
            }
        }
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

        // Check storage for all files
        $totalSize = 0;
        foreach ($this->patientFiles as $f) {
            if (isset($f['temp_path'])) {
                $totalSize += \Illuminate\Support\Facades\Storage::disk('public')->size($f['temp_path']);
            }
        }

        if ($doctor && !$doctor->hasStorageSpace($totalSize)) {
            $this->addError('uploads', __('Storage limit reached for this clinic. Please contact administration.'));
            return;
        }

        $doctorId = auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id;

        $visit = $this->patient->visits()->create([
            'doctor_id' => $doctorId,
            'complaint' => $this->complaint,
            'diagnosis' => $this->diagnosis,
            'history' => $this->investigation,
            'treatment_text' => $this->treatmentText,
            'follow_up_notes' => $this->followUpNotes,
            'parent_visit_id' => $this->parentVisitId,
            'type' => $this->visitType,
            'specialty_data' => $this->dynamicAnswers,
        ]);

        // Process Files
        if ($this->patientFiles) {
            foreach ($this->patientFiles as $fileData) {
                if (!isset($fileData['temp_path'])) continue;
                
                $oldPath = $fileData['temp_path'];
                $fileName = $fileData['name'];
                $newPath = "patient_files/" . basename($oldPath);
                
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->move($oldPath, $newPath);
                    
                    // Optimization
                    \App\Services\FileService::optimizeImageInPlace('public', $newPath);
                    \App\Services\FileService::compressFileInPlace('public', $newPath);
                    
                    $finalSize = \Illuminate\Support\Facades\Storage::disk('public')->size($newPath);
                    if ($doctor) $doctor->increment('used_storage_bytes', $finalSize);

                    PatientFile::create([
                        'patient_id' => $this->patient->id,
                        'visit_id' => $visit->id,
                        'file_path' => $newPath,
                        'file_name' => $fileName,
                        'file_type' => $fileData['type'] ?? 'application/octet-stream',
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        }

        $this->showVisitModal = false;
        $this->resetVisitForm();
        session()->flash('visit_message', __('Visit recorded successfully.'));
    }

    private function resetVisitForm()
    {
        $this->reset([
            'complaint', 'diagnosis', 'investigation', 'treatmentText', 
            'parentVisitId', 'visitType', 'followUpNotes', 
            'dynamicAnswers', 'patientFiles', 'uploads'
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
        if (!$this->patientFiles) {
            $this->addError('uploads', __('Please select files to upload.'));
            return;
        }

        /** @var \App\Models\User $doctor */
        $doctor = auth()->user()->isDoctor() ? auth()->user() : auth()->user()->assignedDoctor;

        foreach ($this->patientFiles as $fileData) {
            $oldPath = $fileData['temp_path'];
            $fileName = $fileData['name'];
            $newPath = "patient_files/" . basename($oldPath);
            
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                $fileSize = \Illuminate\Support\Facades\Storage::disk('public')->size($oldPath);
                
                if ($doctor && !$doctor->hasStorageSpace($fileSize)) {
                    $this->addError('uploads', __('Storage limit reached.'));
                    return;
                }

                \Illuminate\Support\Facades\Storage::disk('public')->move($oldPath, $newPath);
                
                // Optimization
                \App\Services\FileService::optimizeImageInPlace('public', $newPath);
                \App\Services\FileService::compressFileInPlace('public', $newPath);
                
                $finalSize = \Illuminate\Support\Facades\Storage::disk('public')->size($newPath);
                if ($doctor) $doctor->increment('used_storage_bytes', $finalSize);

                PatientFile::create([
                    'patient_id' => $this->patient->id,
                    'file_path' => $newPath,
                    'file_name' => $fileName,
                    'file_type' => $this->fileType ?: ($fileData['type'] ?? 'application/octet-stream'),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        $this->reset(['patientFiles', 'uploads']);
        session()->flash('file_message', __('Files uploaded successfully.'));
    }

    public function deleteFile($fileId)
    {
        $file = PatientFile::findOrFail($fileId);
        
        // Check permissions
        if (!auth()->user()->isDoctor() && !auth()->user()->isSecretary()) {
            return;
        }

        // Subtract storage
        /** @var \App\Models\User $doctor */
        $doctor = auth()->user()->isDoctor() ? auth()->user() : auth()->user()->assignedDoctor;
        if ($doctor && \Illuminate\Support\Facades\Storage::disk('public')->exists($file->file_path)) {
            $size = \Illuminate\Support\Facades\Storage::disk('public')->size($file->file_path);
            $doctor->decrement('used_storage_bytes', min($size, $doctor->used_storage_bytes));
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
        $file->delete();
        session()->flash('file_message', __('File deleted.'));
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
        $this->editAddress = $this->patient->address;
        $this->editWeight = $this->patient->weight;
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
    }

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
            ->join('patient_files', 'visits.id', '=', 'patient_files.visit_id')
            ->select('patient_files.*')
            ->orderBy('patient_files.created_at', 'desc')
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->file_name,
                'path' => $f->file_path,
                'type' => $f->file_type,
                'date' => $f->created_at,
                'source' => 'patient_file',
                'url' => route('files.patient', $f->id),
            ]);

        // Fallback for old treatment_file_path if it exists
        $legacyVisitFiles = $this->patient->visits()
            ->whereNotNull('treatment_file_path')
            ->whereDoesntHave('files')
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

        $allFiles = $medicalFiles->concat($visitFiles)->concat($legacyVisitFiles)->unique('path')->sortByDesc('date');

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
