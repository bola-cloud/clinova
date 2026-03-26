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

    public Patient $patient;
    
    // File Upload properties
    public $newFile;
    public $fileType = 'lab';
    
    // UI Toggles & Edits
    public $isEditingFH = false; // Family History
    public $isEditingPH = false; // Personal History
    public $familyHistoryEdit;
    public $personalHistoryEdit;
    
    // Edit Patient properties
    public $showEditModal = false;
    public $editName, $editPhone, $editAge, $editAddress, $editWeight;

    // Visit Recording properties
    public $showVisitModal = false;
    public $complaint = '';
    public $diagnosis = '';
    public $investigation = '';
    public $treatmentText = '';
    public $treatmentFile = null;
    public $parentVisitId = null;
    public $visitType = 'checkup';

    // Autocomplete Suggestions
    public $complaintSuggestions = [];
    public $diagnosisSuggestions = [];

    // Booking properties
    public $showBookingModal = false;
    public $bookingDoctorId = '';
    public $bookingDate = '';
    public $bookingType = 'checkup';

    public function mount(Patient $patient)
    {
        // Hard security check to prevent accessing other doctors' patients via URL manipulation
        if (auth()->user()->isDoctor() && auth()->id() !== $patient->doctor_id) {
            abort(403, __('Unauthorized access to patient profile.'));
        }
        if (auth()->user()->isSecretary() && auth()->user()->doctor_id !== $patient->doctor_id) {
            abort(403, __('Unauthorized access to patient profile.'));
        }

        $this->patient = $patient->load([
            'files' => fn($q) => $q->orderBy('created_at', 'desc'),
            'appointments.doctor'
        ]);
        
        $this->familyHistoryEdit = $this->patient->family_history;
        $this->personalHistoryEdit = $this->patient->personal_history;
    }

    // Toggle Methods (to be called by wire:click if not using Alpine, but Blade uses Alpine for some)
    // However, we keep placeholders or logic if needed.

    public function updatedComplaint($value)
    {
        if (strlen($value) < 2) {
            $this->complaintSuggestions = [];
            return;
        }
        $this->complaintSuggestions = Visit::where('complaint', 'like', $value.'%')
            ->distinct()
            ->pluck('complaint')
            ->take(5)
            ->toArray();
    }

    public function updatedDiagnosis($value)
    {
        if (strlen($value) < 2) {
            $this->diagnosisSuggestions = [];
            return;
        }
        $this->diagnosisSuggestions = Visit::where('diagnosis', 'like', $value.'%')
            ->distinct()
            ->pluck('diagnosis')
            ->take(5)
            ->toArray();
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

    public function openVisitModal()
    {
        $this->reset(['complaint', 'diagnosis', 'investigation', 'treatmentText', 'treatmentFile', 'parentVisitId', 'visitType']);
        $this->showVisitModal = true;
    }

    public function closeVisitModal()
    {
        $this->showVisitModal = false;
    }

    public function startFollowUp($visitId)
    {
        $this->openVisitModal();
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
        $this->validate([
            'complaint' => 'required|string|max:5000',
            'diagnosis' => 'required|string|max:2000',
            'investigation' => 'nullable|string|max:5000',
            'treatmentText' => 'nullable|string|max:5000',
            'treatmentFile' => 'nullable|file|max:10240',
        ]);

        $treatmentFilePath = null;
        if ($this->treatmentFile) {
            $treatmentFilePath = $this->treatmentFile->store('treatments', 'public');
        }

        $doctorId = auth()->user()->isDoctor() ? auth()->id() : auth()->user()->doctor_id;

        $this->patient->visits()->create([
            'doctor_id' => $doctorId,
            'complaint' => $this->complaint,
            'diagnosis' => $this->diagnosis,
            'history' => $this->investigation,
            'treatment_text' => $this->treatmentText,
            'treatment_file_path' => $treatmentFilePath,
            'parent_visit_id' => $this->parentVisitId,
            'type' => $this->visitType,
        ]);

        $this->closeVisitModal();
        session()->flash('visit_message', __('Visit recorded successfully.'));
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

        $this->validate([
            'newFile' => 'required|file|max:20480',
            'fileType' => 'required|in:lab,investigation,other',
        ]);

        $path = $this->newFile->store('patient-files', 'public');

        $this->patient->files()->create([
            'file_name' => $this->newFile->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $this->fileType,
            'uploaded_by' => auth()->id(),
        ]);

        $this->reset(['newFile', 'fileType']);
        $this->patient->load('files');
        session()->flash('file_message', __('Medical file uploaded successfully.'));
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
        $this->editAge = $this->patient->age;
        $this->editWeight = $this->patient->weight;
        $this->editAddress = $this->patient->address;
        $this->showEditModal = true;
    }

    public function closeEditModal() { $this->showEditModal = false; }

    public function savePatientData()
    {
        $this->validate([
            'editName' => 'required|min:3',
            'editPhone' => 'required|numeric',
            'editAge' => 'nullable|numeric',
            'editWeight' => 'nullable|numeric',
            'editAddress' => 'nullable|string|max:500',
        ]);

        app(\App\Services\PatientService::class)->updatePatient($this->patient->id, [
            'name' => $this->editName,
            'phone' => $this->editPhone,
            'age' => $this->editAge,
            'weight' => $this->editWeight,
            'address' => $this->editAddress,
        ]);

        $this->patient->refresh();
        $this->closeEditModal();
        session()->flash('message', __('Patient data updated successfully.'));
    }

    public function render()
    {
        return view('livewire.shared.patient-profile', [
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
