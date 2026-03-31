<?php

namespace App\Livewire\Secretary;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

#[Layout('layouts.clinic')]
class SecretarySettings extends Component
{
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('success', __('Security settings updated successfully.'));
    }

    public function render()
    {
        return view('livewire.secretary.secretary-settings');
    }
}
