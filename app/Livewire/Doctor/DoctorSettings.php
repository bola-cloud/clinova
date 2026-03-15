<?php

namespace App\Livewire\Doctor;

use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Layout;

#[Layout('layouts.clinic')]
class DoctorSettings extends Component
{
    #[Rule('nullable|numeric|min:0|max:999999')]
    public $consultation_fee;

    #[Rule('nullable|numeric|min:0|max:999999')]
    public $followup_fee;

    public function mount()
    {
        $user = auth()->user();
        $this->consultation_fee = $user->consultation_fee;
        $this->followup_fee = $user->followup_fee;
    }

    public function saveSettings()
    {
        $this->validate();

        $user = auth()->user();
        $user->consultation_fee = $this->consultation_fee ?: null;
        $user->followup_fee = $this->followup_fee ?: null;
        $user->save();

        session()->flash('success', __('Settings saved successfully'));
    }

    public function render()
    {
        return view('livewire.doctor.doctor-settings');
    }
}
