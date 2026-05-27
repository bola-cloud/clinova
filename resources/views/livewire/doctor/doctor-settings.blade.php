<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">{{ __('Doctor Settings') }}</h1>
            <p class="text-gray-500 mt-1">{{ __('Manage your consultation fees and clinic preferences here.') }}</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <form wire:submit="saveSettings" class="p-6 md:p-8">
            <div class="px-6 md:px-8 py-8 border-b border-gray-100 bg-slate-50/50">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    {{ __('Profile Identity') }}
                </h3>

                <div class="flex flex-col md:flex-row items-center gap-8">
                    <!-- Image Preview -->
                    <div class="relative group">
                        <div class="w-32 h-32 rounded-3xl overflow-hidden border-4 border-white shadow-xl bg-gradient-to-tr from-purple-100 to-indigo-100 flex items-center justify-center relative">
                            @if ($profile_image)
                                <img src="{{ $profile_image->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif ($current_image)
                                <img src="{{ asset('storage/' . $current_image) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-4xl font-black text-purple-300">{{ auth()->user()->name[0] ?? '?' }}</span>
                            @endif

                            <!-- Loading Overlay -->
                            <div wire:loading wire:target="profile_image" class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center">
                                <svg class="animate-spin h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <label class="absolute -bottom-2 -right-2 bg-white p-2 rounded-xl shadow-lg border border-gray-100 cursor-pointer hover:bg-purple-50 transition-colors group-hover:scale-110 duration-300">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <input type="file" wire:model="profile_image" class="hidden" accept="image/*">
                        </label>
                    </div>

                    <div class="flex-1 space-y-2 text-center md:text-left">
                        <h4 class="font-bold text-gray-900">{{ __('Profile Photo') }}</h4>
                        <p class="text-xs text-gray-500 leading-relaxed max-w-sm">
                            {{ __('Upload a professional photo to build trust with your patients.') }}
                            <br>
                            {{ __('Recommended: Square image, max 5MB.') }}
                        </p>
                        @error('profile_image') <span class="text-rose-500 text-xs font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ __('Financial Settings') }}
                </h3>

            <div class="space-y-6 max-w-2xl">
                <!-- Consultation Fee -->
                <div class="space-y-1.5">
                    <label class="text-sm font-bold text-gray-700">{{ __('Consultation Fee') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-4' : 'right-0 pr-4' }} flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold">{{ __('EGP') }}</span>
                        </div>
                        <input type="number" step="0.01" min="0" wire:model="consultation_fee" dir="ltr" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm py-3 px-4 {{ app()->getLocale() === 'ar' ? 'pl-12 pr-4 text-right' : 'pr-12 pl-4 text-left' }} shadow-inner transition-all hover:border-purple-300">
                    </div>
                    @error('consultation_fee') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-400 mt-1">{{ __('The standard fee charged for a new checkup.') }}</p>
                </div>

                <!-- Follow-up Fee -->
                <div class="space-y-1.5">
                    <label class="text-sm font-bold text-gray-700">{{ __('Follow-up Fee') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-4' : 'right-0 pr-4' }} flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold">{{ __('EGP') }}</span>
                        </div>
                        <input type="number" step="0.01" min="0" wire:model="followup_fee" dir="ltr" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm py-3 px-4 {{ app()->getLocale() === 'ar' ? 'pl-12 pr-4 text-right' : 'pr-12 pl-4 text-left' }} shadow-inner transition-all hover:border-purple-300">
                    </div>
                    @error('followup_fee') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-400 mt-1">{{ __('The fee charged for a follow-up visit.') }}</p>
                </div>

                <!-- Custom Dynamic Fees -->
                <div class="space-y-3 pt-6 border-t border-dashed border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <label class="text-sm font-black text-gray-800">{{ __('Additional Booking Types & Fees') }}</label>
                        <button type="button" wire:click="addCustomFee" class="w-fit px-3 py-1.5 bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-xs rounded-lg transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            {{ __('Add Type') }}
                        </button>
                    </div>

                    @if(count($customFees) > 0)
                        <div class="space-y-3">
                            @foreach($customFees as $index => $feeItem)
                                <div wire:key="custom-fee-{{ $index }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-slate-50/50 p-3 rounded-2xl border border-gray-100 shadow-sm">
                                    <!-- Name -->
                                    <div class="flex-1">
                                        <input type="text" wire:model="customFees.{{ $index }}.name" placeholder="{{ __('e.g., Urgent Booking, Home Visit') }}" class="w-full bg-white border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-xs py-2 px-3 shadow-inner">
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <!-- Fee -->
                                        <div class="w-32 sm:w-36 min-w-[100px] relative">
                                            <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-3' : 'right-0 pr-3' }} flex items-center pointer-events-none">
                                                <span class="text-gray-400 font-bold text-[10px]">{{ __('EGP') }}</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" wire:model="customFees.{{ $index }}.fee" placeholder="0.00" dir="ltr" class="w-full bg-white border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-xs py-2 {{ app()->getLocale() === 'ar' ? 'pl-8 pr-3 text-right' : 'pr-8 pl-3 text-left' }} shadow-inner">
                                        </div>
                                        <!-- Remove Button -->
                                        <button type="button" wire:click="removeCustomFee({{ $index }})" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors" title="{{ __('Delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 bg-slate-50/50 rounded-2xl border border-dashed border-gray-200 text-center text-xs text-gray-400">
                            {{ __('No additional custom booking types configured. Click "Add Type" to add one.') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-black rounded-xl shadow-xl shadow-indigo-100 transition-all uppercase tracking-widest text-xs flex items-center gap-2 hover:-translate-y-1">
                    <svg wire:loading wire:target="saveSettings" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('Save Settings') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Security Settings -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 md:p-8 border-b border-gray-100 bg-slate-50/50">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                {{ __('Security Settings') }}
            </h3>
        </div>

        <form wire:submit.prevent="updatePassword" class="p-6 md:p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-1.5">
                    <label class="text-sm font-bold text-gray-700">{{ __('Current Password') }}</label>
                    <input type="password" wire:model="current_password" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 text-sm py-3 px-4 shadow-inner">
                    @error('current_password') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-bold text-gray-700">{{ __('New Password') }}</label>
                    <input type="password" wire:model="password" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 text-sm py-3 px-4 shadow-inner">
                    @error('password') <span class="text-rose-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-bold text-gray-700">{{ __('Confirm Password') }}</label>
                    <input type="password" wire:model="password_confirmation" class="w-full bg-slate-50 border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 text-sm py-3 px-4 shadow-inner">
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-slate-900 border border-slate-900 text-white font-black rounded-xl shadow-xl hover:bg-black hover:-translate-y-1 transition-all uppercase tracking-widest text-xs flex items-center gap-2">
                    {{ __('Update Security') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Data Export / Backup Settings -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 md:p-8 border-b border-gray-100 bg-slate-50/50">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                {{ __('Data Backup & Export') }}
            </h3>
        </div>

        <div class="p-6 md:p-8 space-y-6">
            <p class="text-sm text-gray-500 max-w-2xl">
                {{ __('Download a complete backup of all your data including patients, appointments, visits, and medical files in JSON format. This includes all active and soft-deleted records.') }}
            </p>
            
            <div class="flex">
                <button type="button" wire:click="downloadBackup" wire:loading.attr="disabled" class="px-8 py-3 bg-emerald-100 border border-emerald-200 text-emerald-700 hover:bg-emerald-600 hover:text-white font-black rounded-xl shadow-sm transition-all uppercase tracking-widest text-xs flex items-center gap-2 disabled:opacity-55 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    {{ __('Export Backup Data') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Backup Overlay -->
    <div wire:loading wire:target="downloadBackup" class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-300">
        <div class="bg-slate-900 border border-slate-800 p-8 rounded-3xl shadow-2xl flex flex-col items-center gap-5 max-w-sm text-center relative overflow-hidden">
            <!-- Glowing background accent -->
            <div class="absolute -top-10 -left-10 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 -right-10 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl"></div>
            
            <!-- Spinner -->
            <div class="relative w-16 h-16">
                <div class="absolute inset-0 rounded-full border-4 border-slate-800"></div>
                <div class="absolute inset-0 rounded-full border-4 border-t-emerald-500 animate-spin"></div>
            </div>
            <div class="space-y-2 z-10">
                <h3 class="text-lg font-black text-white tracking-tight">{{ __('Preparing Backup...') }}</h3>
                <p class="text-xs text-slate-400 font-medium leading-relaxed">{{ __('This may take a moment as we package your clinical records and files.') }}</p>
            </div>
        </div>
    </div>
</div>
