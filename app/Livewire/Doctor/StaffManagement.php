<?php

namespace App\Livewire\Doctor;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StaffManagement extends Component
{
    public $staff_name = '';
    public $staff_email = '';
    public $staff_password = '';
    
    public $editingStaffId = null;
    public $new_password = '';

    protected function rules()
    {
        return [
            'staff_name' => 'required|string|max:255',
            'staff_email' => 'required|email|unique:users,email',
            'staff_password' => ['required', Password::defaults()],
        ];
    }

    public function createStaff()
    {
        $this->validate();

        User::create([
            'name' => $this->staff_name,
            'email' => $this->staff_email,
            'password' => Hash::make($this->staff_password),
            'role' => 'secretary',
            'doctor_id' => auth()->id(),
        ]);

        $this->reset(['staff_name', 'staff_email', 'staff_password']);
        session()->flash('success', __('Staff account created successfully.'));
    }

    public function deleteStaff($id)
    {
        $staff = User::where('id', $id)->where('doctor_id', auth()->id())->firstOrFail();
        $staff->delete();
        session()->flash('success', __('Staff account deleted successfully.'));
    }

    public function editPassword($id)
    {
        $this->editingStaffId = $id;
        $this->new_password = '';
    }

    public function updatePassword()
    {
        $this->validate([
            'new_password' => ['required', Password::defaults()],
        ]);

        $staff = User::where('id', $this->editingStaffId)->where('doctor_id', auth()->id())->firstOrFail();
        $staff->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->editingStaffId = null;
        $this->new_password = '';
        session()->flash('success', __('Password updated successfully.'));
    }

    public function render()
    {
        return view('livewire.doctor.staff-management', [
            'staffMembers' => auth()->user()->secretaries()->get()
        ])->layout('layouts.clinic', ['title' => __('Clinic Staff')]);
    }
}
