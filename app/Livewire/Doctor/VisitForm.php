<?php

namespace App\Livewire\Doctor;

use App\Models\Appointment;
use App\Models\Visit;
use Livewire\Component;
use Livewire\WithFileUploads;

class VisitForm extends Component
{
    use WithFileUploads;
    use \App\Traits\HasMedicalSuggestions;

    public Appointment $appointment;
    public $complaint, $diagnosis, $history, $treatment_text, $treatment_file;

    public function updatedComplaint($value)
    {
        $this->complaintSuggestions = $this->getMedicalSuggestions('complaint', $value, 'complaints');
    }

    public function updatedDiagnosis($value)
    {
        $this->diagnosisSuggestions = $this->getMedicalSuggestions('diagnosis', $value, 'diagnosis');
    }

    public function updatedHistory($value)
    {
        $this->investigationSuggestions = $this->getMedicalSuggestions('history', $value, 'investigations');
    }

    public function updatedTreatmentText($value)
    {
        $this->treatmentSuggestions = $this->getMedicalSuggestions('treatment_text', $value, 'treatments');
    }

    public function selectSuggestionFor($field, $value)
    {
        $this->$field = $value;
        $suggestionField = $field . 'Suggestions';
        if ($field === 'history') $suggestionField = 'investigationSuggestions';
        if ($field === 'treatment_text') $suggestionField = 'treatmentSuggestions';
        
        $this->$suggestionField = [];
    }


    public function mount(Appointment $appointment)
    {
        $this->authorize('recordVisit', $appointment);

        $this->appointment = $appointment->load('patient');
        // Pre-fill history if patient has previous visits
        $this->history = $this->appointment->patient->family_history;
    }

    public function saveVisit()
    {
        $this->validate([
            'complaint' => 'required',
            'diagnosis' => 'required',
        ]);

        $filePath = null;
        if ($this->treatment_file) {
            $filePath = $this->treatment_file->store('visits', 'public');
        }

        Visit::create([
            'patient_id' => $this->appointment->patient_id,
            'doctor_id' => auth()->id(),
            'complaint' => $this->complaint,
            'diagnosis' => $this->diagnosis,
            'history' => $this->history,
            'treatment_text' => $this->treatment_text,
            'treatment_file_path' => $filePath,
        ]);

        // Mark appointment as seen
        $this->appointment->update(['status' => 'seen']);

        session()->flash('success', 'تم تسجيل الزيارة بنجاح');
        return redirect()->route('doctor.dashboard');
    }

    public function render()
    {
        return view('livewire.doctor.visit-form')
            ->layout('layouts.clinic', ['title' => 'تسجيل كشف جديد']);
    }
}
