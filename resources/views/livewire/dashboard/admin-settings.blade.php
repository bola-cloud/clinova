<?php

use Livewire\Volt\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

new class extends Component
{
    public $clinicName = '';
    public $systemMaintenance = false;
    public $trialDurationDays = 14;

    // Security Properties
    public $current_password = '';
    public $password = '';
    public $password_confirmation = '';

    public function mount()
    {
        $this->clinicName = Setting::get('clinic_name', 'Clinova Smart Clinic');
        $this->systemMaintenance = Setting::get('system_maintenance', false);
        $this->trialDurationDays = Setting::get('trial_duration_days', 14);
    }

    public function saveSettings()
    {
        Setting::set('clinic_name', $this->clinicName);
        Setting::set('system_maintenance', $this->systemMaintenance, 'boolean');
        Setting::set('trial_duration_days', (int) $this->trialDurationDays, 'integer');

        session()->flash('success', __('System settings updated successfully.'));
    }

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
};
?>

<div class="space-y-6">
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-10 border-b border-gray-100">
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('System Control Panel') }}</h3>
            <p class="text-gray-500 font-medium mt-1">{{ __('Manage global application settings and maintenance.') }}</p>
        </div>

        @if (session()->has('success'))
            <div class="px-10 mt-6">
                <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 flex items-center gap-3 animate-slide-in">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold text-sm">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        
        <div class="p-10 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-4">
                    <label class="text-xs font-black text-purple-600 uppercase tracking-[0.2em]">{{ __('General Information') }}</label>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">{{ __('Clinic Brand Name') }}</label>
                        <input type="text" wire:model="clinicName" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">{{ __('Default Trial Duration (Days)') }}</label>
                        <input type="number" wire:model="trialDurationDays" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all">
                    </div>
                    <div class="pt-4">
                        <a href="{{ route('admin.specialties') }}" class="flex items-center gap-3 p-4 bg-purple-50 text-purple-700 rounded-2xl border border-purple-100 hover:bg-purple-100 transition-colors group">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm">{{ __('Doctors Specialties') }}</h4>
                                <p class="text-[10px] font-bold uppercase tracking-wider opacity-60">{{ __('Manage dynamic fields') }}</p>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-xs font-black text-rose-600 uppercase tracking-[0.2em]">{{ __('System Status') }}</label>
                    <div class="flex items-center justify-between p-6 bg-rose-50 rounded-[2rem] border border-rose-100">
                        <div>
                            <h4 class="font-bold text-rose-900">{{ __('Maintenance Mode') }}</h4>
                            <p class="text-xs text-rose-700/70 font-medium">{{ __('Disable access for all non-admin users.') }}</p>
                        </div>
                        <button type="button" 
                                wire:click="$toggle('systemMaintenance')"
                                class="relative inline-flex h-8 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $systemMaintenance ? 'bg-rose-500' : 'bg-gray-200' }}">
                            <span class="pointer-events-none inline-block h-7 w-7 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $systemMaintenance ? (app()->getLocale() === 'ar' ? '-translate-x-6' : 'translate-x-6') : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-10 border-t border-gray-50 flex justify-end">
                <button wire:click="saveSettings" class="px-10 py-4 bg-slate-900 text-white rounded-2xl font-black text-sm shadow-xl hover:bg-black hover:-translate-y-1 transition-all">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-10 border-b border-gray-100">
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Security Settings') }}</h3>
            <p class="text-gray-500 font-medium mt-1">{{ __('Manage your account password and security preferences.') }}</p>
        </div>

        <div class="p-10">
            <form wire:submit.prevent="updatePassword" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">{{ __('Current Password') }}</label>
                        <input type="password" wire:model="current_password" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all">
                        @error('current_password') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">{{ __('New Password') }}</label>
                        <input type="password" wire:model="password" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all">
                        @error('password') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700">{{ __('Confirm Password') }}</label>
                        <input type="password" wire:model="password_confirmation" class="w-full bg-slate-50 border-gray-200 rounded-2xl py-4 px-6 text-sm focus:ring-4 focus:ring-purple-500/10 transition-all">
                    </div>
                </div>

                <div class="pt-10 border-t border-gray-50 flex justify-end">
                    <button type="submit" class="px-10 py-4 bg-purple-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-purple-200 hover:bg-purple-700 hover:-translate-y-1 transition-all">
                        {{ __('Update Security') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
