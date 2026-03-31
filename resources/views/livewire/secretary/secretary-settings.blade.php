<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">{{ __('Account Settings') }}</h1>
            <p class="text-gray-500 mt-1">{{ __('Manage your account security and authentication preferences.') }}</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 flex items-center gap-3 animate-slide-in">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

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
</div>
