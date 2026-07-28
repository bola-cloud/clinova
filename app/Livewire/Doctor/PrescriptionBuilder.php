<?php

namespace App\Livewire\Doctor;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\PrescriptionSetting;

class PrescriptionBuilder extends Component
{
    use WithFileUploads;

    public $backgroundImage;
    public $currentBackgroundImagePath;
    
    // Default elements for the prescription
    public $elements = [
        'patient_name' => ['x' => 10, 'y' => 10, 'width' => 40, 'fontSize' => 14, 'visible' => true, 'label' => 'Patient Name'],
        'patient_age' => ['x' => 60, 'y' => 10, 'width' => 30, 'fontSize' => 14, 'visible' => true, 'label' => 'Patient Age'],
        'date' => ['x' => 10, 'y' => 20, 'width' => 30, 'fontSize' => 12, 'visible' => true, 'label' => 'Date'],
        'diagnosis' => ['x' => 10, 'y' => 30, 'width' => 80, 'fontSize' => 14, 'visible' => true, 'label' => 'Diagnosis'],
        'treatment' => ['x' => 10, 'y' => 45, 'width' => 80, 'fontSize' => 14, 'visible' => true, 'label' => 'Treatment & Instructions'],
        'investigations' => ['x' => 10, 'y' => 70, 'width' => 80, 'fontSize' => 14, 'visible' => true, 'label' => 'Investigations'],
        'canvas' => ['x' => 10, 'y' => 85, 'width' => 80, 'fontSize' => 14, 'visible' => true, 'label' => 'Freehand Board'],
    ];

    public function mount()
    {
        $user = auth()->user();
        if (!$user->isDoctor()) {
            $user = \App\Models\User::find($user->doctor_id);
        }

        $setting = $user->prescriptionSetting;

        if ($setting) {
            $this->currentBackgroundImagePath = $setting->background_image_path;
            if ($setting->elements) {
                // Merge saved elements with default to avoid missing keys if we add new ones later
                $this->elements = array_merge($this->elements, $setting->elements);
            }
        }
    }

    public function updatedBackgroundImage()
    {
        $this->validate([
            'backgroundImage' => 'image|max:5120', // 5MB max
        ]);

        $user = auth()->user();
        $doctor = $user->isDoctor() ? $user : \App\Models\User::find($user->doctor_id);

        $path = $this->backgroundImage->store('prescriptions', 'public');
        
        $setting = PrescriptionSetting::firstOrCreate(
            ['doctor_id' => $doctor->id],
            ['elements' => $this->elements]
        );

        // Delete old image
        if ($setting->background_image_path && Storage::disk('public')->exists($setting->background_image_path)) {
            Storage::disk('public')->delete($setting->background_image_path);
        }

        $setting->background_image_path = $path;
        $setting->save();

        $this->currentBackgroundImagePath = $path;
        $this->reset('backgroundImage');
        
        session()->flash('message', __('Background uploaded successfully!'));
    }

    public function removeBackground()
    {
        $user = auth()->user();
        $doctor = $user->isDoctor() ? $user : \App\Models\User::find($user->doctor_id);

        $setting = PrescriptionSetting::where('doctor_id', $doctor->id)->first();
        if ($setting && $setting->background_image_path) {
            if (Storage::disk('public')->exists($setting->background_image_path)) {
                Storage::disk('public')->delete($setting->background_image_path);
            }
            $setting->background_image_path = null;
            $setting->save();
        }

        $this->currentBackgroundImagePath = null;
        session()->flash('message', __('Background removed successfully!'));
    }

    public function saveLayout($newElements)
    {
        $user = auth()->user();
        $doctor = $user->isDoctor() ? $user : \App\Models\User::find($user->doctor_id);

        $this->elements = $newElements;

        $setting = PrescriptionSetting::firstOrCreate(
            ['doctor_id' => $doctor->id]
        );

        $setting->elements = $this->elements;
        $setting->save();

        session()->flash('message', __('Prescription layout saved successfully!'));
    }

    public function render()
    {
        return view('livewire.doctor.prescription-builder')
            ->layout('layouts.clinic');
    }
}
