<?php

namespace App\Livewire\Doctor;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;

#[Layout('layouts.clinic')]
class DoctorSettings extends Component
{
    use WithFileUploads;

    #[Rule('nullable|image|max:2048')]
    public $profile_image;

    public $current_image;
    #[Rule('nullable|numeric|min:0|max:999999')]
    public $consultation_fee;

    #[Rule('nullable|numeric|min:0|max:999999')]
    public $followup_fee;

    #[Rule('nullable|string|max:255')]
    public $secretary_name;

    #[Rule('nullable|string|max:20')]
    public $secretary_phone;

    public function mount()
    {
        $user = auth()->user();
        $this->consultation_fee = $user->consultation_fee;
        $this->followup_fee = $user->followup_fee;
        $this->secretary_name = $user->secretary_name;
        $this->secretary_phone = $user->secretary_phone;
        $this->current_image = $user->profile_image;
    }

    public function saveSettings()
    {
        $this->validate();

        $user = auth()->user();
        
        if ($this->profile_image) {
            $path = $this->profile_image->store('profile-images', 'public');
            $user->profile_image = $path;
            $this->current_image = $path;
            $this->profile_image = null;
        }

        $user->consultation_fee = $this->consultation_fee ?: null;
        $user->followup_fee = $this->followup_fee ?: null;
        $user->secretary_name = $this->secretary_name;
        $user->secretary_phone = $this->secretary_phone;
        $user->save();

        session()->flash('success', __('Settings saved successfully'));
    }

    public function render()
    {
        return view('livewire.doctor.doctor-settings');
    }
}
