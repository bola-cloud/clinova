<?php

namespace App\Livewire\Shared;

use App\Models\Patient;
use App\Models\PatientFile;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class PatientProfile extends Component
{
    use WithFileUploads;

    public Patient $patient;
    
    // File Upload properties
    public $newFile;
    public $fileType = 'lab';
    
    // Family History properties
    public $isEditingHistory = false;
    public $familyHistoryEdit;
    
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

    // Booking properties
    public $showBookingModal = false;
    public $bookingDoctorId = '';
    public $bookingDate = '';
    public $bookingType = 'checkup';

    public function mount(Patient $patient)
    {
        $this->patient = $patient->load(['visits.doctor', 'files', 'appointments.doctor']);
        $this->familyHistoryEdit = $this->patient->family_history;
    }

    public function uploadFile()
    {
        $this->validate([
            'newFile' => 'required|file|max:10240', // 10MB Max
            'fileType' => 'required|in:xray,lab,other',
        ]);

        $path = $this->newFile->store('patient-files', 'public');

        $this->patient->files()->create([
            'file_name' => $this->newFile->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $this->fileType,
            'uploaded_by' => auth()->id(),
        ]);

        $this->reset('newFile', 'fileType');
        $this->patient->load('files'); // Refresh files relation
        session()->flash('message', __('File uploaded successfully.'));
    }

    public function openVisitModal()
    {
        $this->reset(['complaint', 'diagnosis', 'investigation', 'treatmentText', 'treatmentFile']);
        $this->showVisitModal = true;
    }

    public function closeVisitModal()
    {
        $this->showVisitModal = false;
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
            $treatmentFilePath = $this->treatmentFile->store('visit-attachments', 'public');
        }

        $this->patient->visits()->create([
            'doctor_id' => auth()->id(),
            'complaint' => $this->complaint,
            'diagnosis' => $this->diagnosis,
            'history' => $this->investigation,
            'treatment_text' => $this->treatmentText,
            'treatment_file_path' => $treatmentFilePath,
        ]);

        $this->closeVisitModal();
        $this->patient->load('visits'); // Refresh visits relation
        session()->flash('message', __('Visit recorded successfully.'));
    }

    public function saveFamilyHistory()
    {
        $this->validate([
            'familyHistoryEdit' => 'nullable|string|max:2000',
        ]);

        $this->patient->update([
            'family_history' => $this->familyHistoryEdit
        ]);

        $this->isEditingHistory = false;
        session()->flash('message', __('Family history updated.'));
    }

    public function openBooking()
    {
        // Auto-select doctor: if doctor, select self. If secretary/admin and only 1 doctor, select that one.
        if (auth()->user()->role === 'doctor') {
            $this->bookingDoctorId = auth()->id();
        } else {
            $doctors = \App\Models\User::where('role', 'doctor')->get();
            $this->bookingDoctorId = $doctors->count() === 1 ? $doctors->first()->id : '';
        }
        
        $this->bookingDate = now()->format('Y-m-d');
        $this->bookingType = 'checkup';
        $this->showBookingModal = true;
    }

    public function closeBookingModal()
    {
        $this->showBookingModal = false;
    }

    public function confirmBooking()
    {
        $this->validate([
            'bookingDoctorId' => 'required|exists:users,id',
            'bookingDate' => 'required|date',
            'bookingType' => 'required|in:checkup,follow_up',
        ]);

        app(\App\Services\AppointmentService::class)->bookAppointment([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->bookingDoctorId,
            'scheduled_at' => \Carbon\Carbon::parse($this->bookingDate)->startOfDay(),
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

    public function closeEditModal()
    {
        $this->showEditModal = false;
    }

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
            'doctors' => auth()->user()->role === 'doctor' 
                ? \App\Models\User::where('id', auth()->id())->get() 
                : \App\Models\User::where('role', 'doctor')->get()
        ])->layout('layouts.clinic', ['title' => __('Patient Profile')]);
    }
}
