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
</div>
