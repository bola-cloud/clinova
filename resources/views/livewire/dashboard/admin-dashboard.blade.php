<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\Specialty;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    use WithPagination;

    public $search = '';
    
    // Doctor creation
    public $showCreateModal = false;
    public $new_name = '';
    public $new_email = '';
    public $new_password = '';
    public $new_max_patients = 0;
    public $new_max_storage_gb = 0;
    public $new_specialty_id = '';
    
    public $editingDoctorId = null;
    public $editName = '';
    public $editEmail = '';
    public $editPassword = '';
    public $editSpecialtyId = '';
    public $editMaxPatients = 0;
    public $editMaxStorageGb = 0;
    public $canEditSpecialty = true;

    // Staff management
    public $managingDoctorId = null;
    public $editingStaffId = null;
    public $staff_name = '';
    public $staff_email = '';
    public $staff_password = '';

    public function with()
    {
        $baseQuery = User::where('role', 'doctor')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%'.$this->search.'%')->orWhere('email', 'like', '%'.$this->search.'%'));

        return [
            'doctors' => $baseQuery->withCount('patients')->paginate(10),
            'managingDoctor' => $this->managingDoctorId ? User::find($this->managingDoctorId) : null,
            'specialties' => Specialty::all(),
            'stats' => [
                'total_doctors' => User::where('role', 'doctor')->count(),
                'total_patients' => Patient::count(),
                'total_storage_mb' => User::where('role', 'doctor')->sum('used_storage_bytes') / (1024 * 1024),
            ]
        ];
    }

    public function toggleSubscription($doctorId)
    {
        $doctor = User::findOrFail($doctorId);
        $active = !$doctor->subscription_active;
        $doctor->update([
            'subscription_active' => $active,
            'subscription_expires_at' => $active ? now()->addMonth() : null
        ]);
    }

    public function editDoctor($doctorId)
    {
        $doctor = User::withCount('patients')->findOrFail($doctorId);
        $this->editingDoctorId = $doctorId;
        $this->editName = $doctor->name;
        $this->editEmail = $doctor->email;
        $this->editSpecialtyId = $doctor->specialty_id;
        $this->editMaxPatients = $doctor->max_patients;
        $this->editMaxStorageGb = $doctor->max_storage_gb;
        $this->canEditSpecialty = ($doctor->patients_count == 0);
    }

    public function saveDoctor()
    {
        $this->validate([
            'editName' => 'required|min:3',
            'editEmail' => 'required|email|unique:users,email,' . $this->editingDoctorId,
            'editPassword' => 'nullable|min:6',
            'editMaxPatients' => 'nullable|numeric|min:0',
            'editMaxStorageGb' => 'nullable|numeric|min:0',
            'editSpecialtyId' => 'required|exists:specialties,id',
        ]);

        $doctor = User::findOrFail($this->editingDoctorId);
        
        $data = [
            'name' => $this->editName,
            'email' => $this->editEmail,
            'max_patients' => $this->editMaxPatients,
            'max_storage_gb' => $this->editMaxStorageGb
        ];

        if ($this->editPassword) {
            $data['password'] = Hash::make($this->editPassword);
        }

        if ($this->canEditSpecialty) {
            $data['specialty_id'] = $this->editSpecialtyId;
        }

        $doctor->update($data);
        
        $this->cancelEdit();
        session()->flash('success', __('Details updated for Dr.') . ' ' . $doctor->name);
    }

    public function cancelEdit()
    {
        $this->editingDoctorId = null;
        $this->reset(['editName', 'editEmail', 'editPassword', 'editSpecialtyId', 'editMaxPatients', 'editMaxStorageGb', 'canEditSpecialty']);
    }

    public function manageStaff($doctorId)
    {
        $this->managingDoctorId = $doctorId;
        $this->resetStaffForm();
    }

    public function editStaff($staffId)
    {
        $staff = User::where('role', 'secretary')->where('id', $staffId)->firstOrFail();
        $this->editingStaffId = $staffId;
        $this->staff_name = $staff->name;
        $this->staff_email = $staff->email;
        $this->staff_password = '';
    }

    public function saveSecretary()
    {
        $this->validate([
            'staff_name' => 'required|min:3',
            'staff_email' => 'required|email|unique:users,email,' . ($this->editingStaffId ?? 'NULL'),
            'staff_password' => $this->editingStaffId ? 'nullable|min:6' : 'required|min:6',
        ]);

        if ($this->editingStaffId) {
            $staff = User::where('role', 'secretary')->where('id', $this->editingStaffId)->firstOrFail();
            $data = [
                'name' => $this->staff_name,
                'email' => $this->staff_email,
            ];
            if ($this->staff_password) {
                $data['password'] = Hash::make($this->staff_password);
            }
            $staff->update($data);
            session()->flash('success', __('Secretary updated successfully.'));
        } else {
            User::create([
                'name' => $this->staff_name,
                'email' => $this->staff_email,
                'password' => Hash::make($this->staff_password),
                'role' => 'secretary',
                'doctor_id' => $this->managingDoctorId,
            ]);
            session()->flash('success', __('Secretary account created.'));
        }

        $this->resetStaffForm();
    }

    public function deleteDoctorSecretary($secId)
    {
        User::where('role', 'secretary')->where('id', $secId)->delete();
        session()->flash('success', __('Secretary account deleted.'));
        if ($this->editingStaffId == $secId) {
            $this->resetStaffForm();
        }
    }

    public function resetStaffForm()
    {
        $this->editingStaffId = null;
        $this->reset(['staff_name', 'staff_email', 'staff_password']);
    }

    public function closeStaffModal()
    {
        $this->managingDoctorId = null;
        $this->resetStaffForm();
    }

    public function deleteDoctor($doctorId)
    {
        $doctor = User::findOrFail($doctorId);
        $name = $doctor->name;
        $doctor->delete();
        session()->flash('success', __('Doctor account and all associated data for Dr.') . ' ' . $name . ' ' . __('have been permanently deleted.'));
    }

    public function createDoctor()
    {
        $this->validate([
            'new_name' => 'required|min:3',
            'new_email' => 'required|email|unique:users,email',
            'new_password' => 'required|min:6',
            'new_max_patients' => 'nullable|numeric|min:0',
            'new_max_storage_gb' => 'nullable|numeric|min:0',
            'new_specialty_id' => 'required|exists:specialties,id',
        ]);

        User::create([
            'name' => $this->new_name,
            'email' => $this->new_email,
            'password' => Hash::make($this->new_password),
            'role' => 'doctor',
            'specialty_id' => $this->new_specialty_id,
            'subscription_active' => true,
            'max_patients' => $this->new_max_patients ?: 0,
            'max_storage_gb' => $this->new_max_storage_gb ?: 0,
            'subscription_plan' => 'trial',
            'subscription_price' => 0,
            'is_paid' => false,
            'subscription_start_at' => now(),
            'subscription_expires_at' => now()->addDays(Setting::get('trial_duration_days', 14)),
        ]);

        $this->reset(['showCreateModal', 'new_name', 'new_email', 'new_password', 'new_max_patients', 'new_max_storage_gb', 'new_specialty_id']);
        session()->flash('success', __('Doctor account created successfully.'));
    }
};
?>

<div class="space-y-6">
    <!-- System Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">{{ __('Total Doctors') }}</p>
                <h4 class="text-2xl font-black text-slate-900">{{ number_format($stats['total_doctors']) }}</h4>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">{{ __('Total Patients') }}</p>
                <h4 class="text-2xl font-black text-slate-900">{{ number_format($stats['total_patients']) }}</h4>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase">{{ __('System Storage') }}</p>
                <h4 class="text-2xl font-black text-slate-900 leading-none flex items-baseline gap-1" dir="ltr">
                    <span>{{ number_format($stats['total_storage_mb'], 1) }}</span>
                    <span class="text-sm font-black text-emerald-600 uppercase">MB</span>
                </h4>
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="font-black text-xl text-slate-900 tracking-tight">{{ __('Clinic & Doctor Management') }}</h3>
            <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                <div class="relative w-full md:w-80">
                    <input wire:model.live="search" type="text" placeholder="{{ __('Search doctors...') }}" 
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border-gray-100 rounded-2xl focus:ring-2 focus:ring-purple-500 text-sm italic">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button wire:click="$set('showCreateModal', true)" class="w-full md:w-auto px-6 py-3 bg-slate-900 text-white rounded-2xl font-black text-sm flex items-center justify-center gap-2 hover:bg-black transition-all shadow-lg shadow-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                    {{ __('Add New Doctor') }}
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                <thead class="bg-slate-50/50 text-gray-500 text-xs font-black uppercase tracking-widest">
                    <tr>
                        <th class="px-6 py-5">{{ __('Doctor / Clinic') }}</th>
                        <th class="px-6 py-5">{{ __('Usage Statistics') }}</th>
                        <th class="px-6 py-5 text-center">{{ __('Subscription') }}</th>
                        <th class="px-6 py-5 text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($doctors as $doctor)
                    <tr class="hover:bg-purple-50/30 transition-colors group">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gradient-to-tr from-purple-500 to-indigo-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg border-2 border-white">
                                    {{ mb_substr($doctor->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-900 leading-none mb-1">{{ $doctor->name }}</h4>
                                    <p class="text-xs text-gray-500 font-medium">{{ $doctor->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        @php
                                            $patientsPercent = $doctor->max_patients > 0 ? ($doctor->patients_count / $doctor->max_patients) * 100 : 0;
                                            $storagePercent = $doctor->max_storage_gb > 0 ? (($doctor->used_storage_bytes / (1024*1024*1024)) / $doctor->max_storage_gb) * 100 : 0;
                                        @endphp
                                        <div class="h-full bg-blue-500 rounded-full bg-gradient-to-r from-blue-400 to-blue-600" style="width: {{ min($patientsPercent, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-black text-gray-700 shrink-0 uppercase tracking-tighter" dir="ltr">
                                        {{ $doctor->patients_count }} / {{ $doctor->max_patients ?: '∞' }} <span class="ml-1" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">{{ __('Patients') }}</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600" style="width: {{ min($storagePercent, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-black text-gray-700 shrink-0 uppercase tracking-tighter" dir="ltr">
                                        {{ number_format($doctor->used_storage_bytes / (1024*1024), 2) }} MB / {{ $doctor->max_storage_gb ?: '∞' }} GB
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex flex-col items-center gap-1">
                                @if($doctor->subscription_active)
                                    <span class="px-3 py-1 {{ $doctor->subscription_plan === 'trial' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }} text-[10px] font-black rounded-full uppercase tracking-widest shadow-sm">
                                        {{ __($doctor->subscription_plan === 'trial' ? 'Trial' : ($doctor->subscription_plan === 'monthly' ? 'Monthly' : 'Yearly')) }}
                                    </span>
                                    <div class="flex flex-col items-center">
                                        <span class="text-[10px] font-black {{ $doctor->is_paid ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ number_format($doctor->subscription_price, 2) }} EGP ({{ $doctor->is_paid ? __('Collected') : __('Not Collected') }})
                                        </span>
                                        <span class="text-[9px] text-gray-400 font-bold">{{ $doctor->subscription_expires_at?->format('Y-m-d') }}</span>
                                    </div>
                                @else
                                    <span class="px-3 py-1 bg-rose-100 text-rose-700 text-[10px] font-black rounded-full uppercase tracking-widest shadow-sm">{{ __('Inactive') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.doctor.subscriptions', $doctor->id) }}" wire:navigate class="p-2.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-xl transition-all" title="{{ __('Manage Subscription') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                </a>
                                <button wire:click="manageStaff({{ $doctor->id }})" class="p-2.5 bg-purple-50 text-purple-600 hover:bg-purple-600 hover:text-white rounded-xl transition-all" title="{{ __('Manage Staff') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </button>
                                <button wire:click="editDoctor({{ $doctor->id }})" class="p-2.5 bg-slate-50 text-slate-600 hover:bg-slate-900 hover:text-white rounded-xl transition-all" title="{{ __('Edit Doctor Details') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button wire:click="toggleSubscription({{ $doctor->id }})" 
                                        class="p-2.5 rounded-xl transition-all {{ $doctor->subscription_active ? 'bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white' }}"
                                        title="{{ $doctor->subscription_active ? __('Deactivate') : __('Activate') }}">
                                    @if($doctor->subscription_active)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    @endif
                                </button>
                                <button wire:click="deleteDoctor({{ $doctor->id }})" 
                                        wire:confirm="{{ __('Permanently delete this doctor and all their associated patients, visits, and staff records?') }}"
                                        class="p-2.5 bg-rose-100 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition-all" 
                                        title="{{ __('Delete Doctor Permanently') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-20 text-center text-gray-400 font-bold italic">{{ __('No doctors found in the system.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($doctors->hasPages())
        <div class="p-6 border-t border-gray-50 bg-slate-50/30">
            {{ $doctors->links() }}
        </div>
        @endif
    </div>

    <!-- Edit Quotas Modal -->
    @if($editingDoctorId)
    <div class="fixed inset-0 z-50 flex justify-center items-start overflow-y-auto p-4 md:p-10 bg-slate-900/40 backdrop-blur-sm animate-fade-in">
        <div wire:click="cancelEdit" class="fixed inset-0"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-md shadow-2xl overflow-hidden border border-white animate-zoom-in relative my-8">
            <div class="p-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Edit Doctor Details') }}</h3>
                    <button wire:click="cancelEdit" class="p-2 text-gray-400 hover:text-rose-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit="saveDoctor" class="space-y-6">
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-purple-600 uppercase tracking-[0.2em]">{{ __('Account Details') }}</h4>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('Name') }}</label>
                            <input type="text" wire:model="editName" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500 transition-all">
                            @error('editName') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('Email') }}</label>
                            <input type="email" wire:model="editEmail" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500 transition-all">
                            @error('editEmail') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('New Password (Optional)') }}</label>
                            <input type="password" wire:model="editPassword" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500 transition-all">
                            @error('editPassword') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('Doctor Specialty') }}</label>
                            <select wire:model="editSpecialtyId" @disabled(!$canEditSpecialty) class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500 transition-all {{ !$canEditSpecialty ? 'opacity-60 cursor-not-allowed' : '' }}">
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                                @endforeach
                            </select>
                            @if(!$canEditSpecialty)
                                <p class="text-[9px] text-amber-600 font-bold mt-1 italic">{{ __('Specialty is locked because this doctor already has patient records.') }}</p>
                            @endif
                            @error('editSpecialtyId') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-dashed border-gray-100">
                        <h4 class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em]">{{ __('Quotas & Limits') }}</h4>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">{{ __('Maximum Patients') }} (0 = {{ __('Infinite') }})</label>
                            <div class="relative">
                                <input type="number" wire:model="editMaxPatients" 
                                       class="w-full bg-slate-50 border-gray-100 rounded-2xl py-4 px-5 text-sm font-black focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 transition-all text-center">
                                <span class="absolute {{ app()->getLocale() === 'ar' ? 'left-5 text-left' : 'right-5 text-right' }} top-1/2 -translate-y-1/2 text-gray-400 text-xs font-bold">{{ __('Slot') }}</span>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">{{ __('Maximum Storage') }} (GB, 0 = {{ __('Infinite') }})</label>
                            <div class="relative">
                                <input type="number" step="0.1" wire:model="editMaxStorageGb" 
                                       class="w-full bg-slate-50 border-gray-100 rounded-2xl py-4 px-5 text-sm font-black focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all text-center">
                                <span class="absolute {{ app()->getLocale() === 'ar' ? 'left-5 text-left' : 'right-5 text-right' }} top-1/2 -translate-y-1/2 text-gray-400 text-xs font-bold">GB</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex gap-3">
                        <button type="button" wire:click="cancelEdit" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-sm hover:bg-slate-200 transition-all">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="flex-[2] py-4 bg-purple-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-purple-200 hover:bg-purple-700 hover:-translate-y-1 transition-all">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Create Doctor Modal -->
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex justify-center items-start overflow-y-auto p-4 md:p-10 bg-slate-900/40 backdrop-blur-sm animate-fade-in">
        <div wire:click="$set('showCreateModal', false)" class="fixed inset-0"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-2xl shadow-2xl overflow-hidden border border-white animate-zoom-in relative my-8">
            <div class="p-10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Add New Doctor') }}</h3>
                        <p class="text-xs text-gray-400 font-medium italic">{{ __('Create a new doctor account with custom quotas.') }}</p>
                    </div>
                    <button wire:click="$set('showCreateModal', false)" class="p-2 text-gray-400 hover:text-rose-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form wire:submit="createDoctor" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black text-purple-600 uppercase tracking-[0.2em]">{{ __('Doctor Account Details') }}</h4>
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('Name') }}</label>
                                <input type="text" wire:model="new_name" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500">
                                @error('new_name') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('Email') }}</label>
                                <input type="email" wire:model="new_email" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500">
                                @error('new_email') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('Password') }}</label>
                                <input type="password" wire:model="new_password" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500">
                                @error('new_password') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('Doctor Specialty') }} <span class="text-rose-500">*</span></label>
                                <select wire:model="new_specialty_id" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500">
                                    <option value="">{{ __('Select Specialty') }}</option>
                                    @foreach($specialties as $specialty)
                                        <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                                    @endforeach
                                </select>
                                @error('new_specialty_id') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em]">{{ __('Initial Quotas') }}</h4>
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('Maximum Patients') }} (0 = {{ __('Infinite') }})</label>
                                <input type="number" wire:model="new_max_patients" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-gray-500 uppercase">{{ __('Maximum Storage') }} (GB, 0 = {{ __('Infinite') }})</label>
                                <input type="number" step="0.1" wire:model="new_max_storage_gb" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                            </div>
                            
                            <p class="text-[10px] text-gray-400 font-medium italic bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                                {{ __('Zero or empty values will grant the doctor unlimited usage capacity for that quota.') }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-6 flex gap-3">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="flex-1 py-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-sm hover:bg-slate-200 transition-all">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="flex-[2] py-4 bg-slate-900 text-white rounded-2xl font-black text-sm shadow-xl hover:bg-black hover:-translate-y-1 transition-all">
                            {{ __('Create Doctor Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Manage Staff Modal -->
    @if($managingDoctorId && $managingDoctor)
    <div class="fixed inset-0 z-50 flex justify-center items-start overflow-y-auto p-4 md:p-10 bg-slate-900/40 backdrop-blur-sm animate-fade-in">
        <div wire:click="closeStaffModal" class="fixed inset-0"></div>
        <div class="bg-white rounded-[2.5rem] w-full max-w-2xl shadow-2xl overflow-hidden border border-white animate-zoom-in relative my-8">
            <div class="p-10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Clinic Staff') }} - {{ $managingDoctor->name }}</h3>
                        <p class="text-xs text-gray-400 font-medium italic">{{ __('Manage login credentials for this clinic\'s secretaries.') }}</p>
                    </div>
                    <button wire:click="closeStaffModal" class="p-2 text-gray-400 hover:text-rose-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Staff List -->
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-purple-600 uppercase tracking-[0.2em] mb-4">{{ __('Current Staff') }}</h4>
                        <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($managingDoctor->secretaries as $sec)
                                <div class="p-4 bg-slate-50 rounded-2xl border border-gray-100 flex items-center justify-between group transition-all hover:bg-white hover:shadow-md">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center font-black text-sm">
                                            {{ mb_substr($sec->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-900 leading-tight">{{ $sec->name }}</h5>
                                            <p class="text-[10px] text-gray-500">{{ $sec->email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="editStaff({{ $sec->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button wire:click="deleteDoctorSecretary({{ $sec->id }})" wire:confirm="{{ __('Permanently delete this secretary account?') }}" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 italic text-center py-8">{{ __('No staff added yet.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Add/Edit Form -->
                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] mb-4">
                            {{ $editingStaffId ? __('Edit Staff Member') : __('Add New Secretary') }}
                        </h4>
                        
                        <form wire:submit="saveSecretary" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-500 uppercase px-1">{{ __('Name') }}</label>
                                <input type="text" wire:model="staff_name" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                                @error('staff_name') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-500 uppercase px-1">{{ __('Email') }}</label>
                                <input type="email" wire:model="staff_email" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                                @error('staff_email') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-500 uppercase px-1">
                                    {{ $editingStaffId ? __('New Password (Optional)') : __('Password') }}
                                </label>
                                <input type="password" wire:model="staff_password" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                                @error('staff_password') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-4 flex gap-2">
                                @if($editingStaffId)
                                    <button type="button" wire:click="resetStaffForm" class="flex-1 py-3 bg-slate-100 text-slate-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">
                                        {{ __('Cancel') }}
                                    </button>
                                @endif
                                <button type="submit" class="flex-[2] py-3 bg-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-black hover:-translate-y-1 transition-all shadow-lg shadow-slate-200">
                                    {{ $editingStaffId ? __('Save Changes') : __('Create Account') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>