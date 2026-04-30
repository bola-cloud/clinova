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
    public $complaint, $diagnosis, $history, $treatment_text;
    public $uploads = []; // Dedicated property for the input field
    public $visitFiles = []; // For storing temp file data
    public $specialtyFields = [];
    public $dynamicAnswers = [];
    public $follow_up_notes;

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

    public function updatedUploads()
    {
        $this->validate([
            'uploads.*' => 'nullable|file|max:2048', // 2MB max
        ], [
            'uploads.*.max' => __('The file size must not exceed 2MB.'),
        ]);

        foreach ($this->uploads as $file) {
            // Save to persistent temp directory immediately
            $tempPath = $file->store('temp_visit_files', 'public');
            
            $this->visitFiles[] = [
                'name' => $file->getClientOriginalName(),
                'type' => $file->getClientMimeType(),
                'temp_path' => $tempPath,
                'id' => uniqid() // For stable wire:key
            ];
        }

        $this->reset(['uploads']);
    }

    public function deleteTempFile($fileId)
    {
        foreach ($this->visitFiles as $index => $fileData) {
            if ($fileData['id'] === $fileId) {
                if (isset($fileData['temp_path'])) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($fileData['temp_path']);
                }
                unset($this->visitFiles[$index]);
                break;
            }
        }
        $this->visitFiles = array_values($this->visitFiles);
    }

    public function mount(Appointment $appointment)
    {
        $this->authorize('recordVisit', $appointment);

        $this->appointment = $appointment->load('patient');
        // Pre-fill history if patient has previous visits
        $this->history = $this->appointment->patient->family_history;

        // Load Specialty Fields
        if ($doctor = auth()->user()) {
            if ($doctor->specialty) {
                $this->specialtyFields = $doctor->specialty->fields;
                foreach ($this->specialtyFields as $field) {
                    $this->dynamicAnswers[$field->id] = $field->type === 'multi_select' ? [] : '';
                }
            }
        }
    }

    public function saveVisit()
    {
        $rules = [
            'complaint' => 'nullable',
            'diagnosis' => 'nullable',
        ];

        $messages = [
            'complaint.required' => __('The complaint field is required.'),
            'diagnosis.required' => __('The diagnosis field is required.'),
        ];

        foreach ($this->specialtyFields as $field) {
            $rules['dynamicAnswers.' . $field->id] = 'nullable';
        }

        $this->validate($rules, $messages, [
            'dynamicAnswers.*' => __('Specialty Field'),
        ]);

        $visit = Visit::create([
            'patient_id' => $this->appointment->patient_id,
            'doctor_id' => auth()->id(),
            'complaint' => $this->complaint,
            'diagnosis' => $this->diagnosis,
            'history' => $this->history,
            'treatment_text' => $this->treatment_text,
            'follow_up_notes' => $this->follow_up_notes,
            'specialty_data' => $this->dynamicAnswers,
        ]);

        // Handle Multiple File Uploads
        if ($this->visitFiles) {
            $totalSize = 0;
            foreach ($this->visitFiles as $fileData) {
                if (!isset($fileData['temp_path'])) continue;
                
                $oldPath = $fileData['temp_path'];
                $fileName = $fileData['name'];
                $newPath = "patient_files/" . basename($oldPath);
                
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    // Optimization & Compression
                    \App\Services\FileService::optimizeImageInPlace('public', $oldPath);
                    \App\Services\FileService::compressFileInPlace('public', $oldPath);
                    
                    $finalSize = \Illuminate\Support\Facades\Storage::disk('public')->size($oldPath);
                    
                    if (auth()->user()->hasStorageSpace($finalSize)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->move($oldPath, $newPath);
                        
                        \App\Models\PatientFile::create([
                            'patient_id' => $this->appointment->patient_id,
                            'visit_id' => $visit->id,
                            'file_path' => $newPath,
                            'file_name' => $fileName,
                            'file_type' => 'prescription',
                            'uploaded_by' => auth()->id(),
                        ]);
                        
                        $totalSize += $finalSize;
                    } else {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                    }
                }
            }
            auth()->user()->increment('used_storage_bytes', $totalSize);
        }

        // Mark appointment as seen
        $this->appointment->update(['status' => 'seen']);

        session()->flash('success', __('Visit recorded successfully.'));
        return redirect()->route('doctor.dashboard');
    }

    public function render()
    {
        return view('livewire.doctor.visit-form')
            ->layout('layouts.clinic', ['title' => __('Record Medical Visit')]);
    }
}
