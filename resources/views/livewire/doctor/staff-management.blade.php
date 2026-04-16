<div class="space-y-8 animate-fade-in" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('Clinic Staff Management') }}</h2>
            <p class="text-gray-500 font-medium mt-1">{{ __('Manage and monitor your clinic\'s secretary accounts.') }}</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-3xl flex items-center gap-3 animate-slide-up">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            <span class="font-black text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add New Staff Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 sticky top-28">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ __('Add New Staff') }}</h3>
                </div>

                <form wire:submit="createStaff" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{{ __('Full Name') }}</label>
                        <input type="text" wire:model="staff_name" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500 transition-all" placeholder="Sarah Johnson">
                        @error('staff_name') <span class="text-rose-500 text-[10px] font-bold ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{{ __('Email Address') }}</label>
                        <input type="email" wire:model="staff_email" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500 transition-all" placeholder="sarah@clinova.com">
                        @error('staff_email') <span class="text-rose-500 text-[10px] font-bold ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{{ __('Password') }}</label>
                        <input type="password" wire:model="staff_password" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-3 px-5 text-sm font-bold focus:ring-2 focus:ring-purple-500 transition-all">
                        @error('staff_password') <span class="text-rose-500 text-[10px] font-bold ml-1">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full py-4 bg-purple-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-purple-100 hover:bg-purple-700 hover:-translate-y-1 transition-all">
                        {{ __('Create Account') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Staff List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ __('Active Staff Members') }}</h3>
                    <span class="px-4 py-1 bg-purple-50 text-purple-600 text-[10px] font-black rounded-full uppercase tracking-widest">{{ $staffMembers->count() }} {{ __('Total') }}</span>
                </div>

                @if($staffMembers->isEmpty())
                    <div class="p-20 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-slate-300">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h4 class="text-lg font-black text-slate-400">{{ __('No staff accounts found') }}</h4>
                        <p class="text-sm text-gray-400 mt-1">{{ __('Create your first secretary account to get started.') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                            <thead class="bg-slate-50/50 text-gray-500 text-[10px] font-black uppercase tracking-widest">
                                <tr>
                                    <th class="px-8 py-5">{{ __('Staff Member') }}</th>
                                    <th class="px-8 py-5 text-center">{{ __('Access Role') }}</th>
                                    <th class="px-8 py-5 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($staffMembers as $member)
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-2xl flex items-center justify-center font-black text-lg shadow-lg group-hover:scale-110 transition-all">
                                                    {{ mb_substr($member->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <h4 class="font-black text-slate-900 leading-none mb-1 text-base">{{ $member->name }}</h4>
                                                    <p class="text-xs text-gray-400 font-medium">{{ $member->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-full uppercase tracking-widest border border-blue-100">
                                                {{ __('Desk Staff') }}
                                            </span>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button wire:click="editPassword({{ $member->id }})" class="p-2.5 bg-slate-900 text-white hover:bg-black rounded-xl transition-all shadow-sm" title="{{ __('Change Password') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                                </button>
                                                <button wire:confirm="{{ __('Permanently delete this account?') }}" wire:click="deleteStaff({{ $member->id }})" class="p-2.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100" title="{{ __('Delete Account') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Password Reset Modal -->
    @if($editingStaffId)
        @php $editingUser = \App\Models\User::find($editingStaffId); @endphp
        <div class="fixed inset-0 z-[100] flex justify-center items-start overflow-y-auto p-4 md:p-10 bg-slate-900/60 backdrop-blur-md animate-fade-in">
            <div wire:click="$set('editingStaffId', null)" class="fixed inset-0"></div>
            <div class="bg-white rounded-[3rem] w-full max-w-md shadow-2xl overflow-hidden animate-zoom-in border border-white/20 relative my-8">
                <div class="p-10">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Change Password') }}</h3>
                            <p class="text-xs text-gray-400 font-medium italic mt-1">{{ $editingUser ? $editingUser->name : '' }}</p>
                        </div>
                        <button wire:click="$set('editingStaffId', null)" class="p-2 text-gray-300 hover:text-rose-500 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form wire:submit="updatePassword" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">{{ __('New Password') }}</label>
                            <input type="password" wire:model="new_password" class="w-full bg-slate-50 border-gray-100 rounded-2xl py-4 px-6 text-sm font-bold focus:ring-2 focus:ring-purple-500 transition-all">
                            @error('new_password') <span class="text-rose-500 text-[10px] font-bold ml-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-4 flex flex-col gap-3">
                            <button type="submit" class="w-full py-4 bg-purple-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-purple-100 hover:bg-purple-700 hover:-translate-y-1 transition-all">
                                {{ __('Update Password') }}
                            </button>
                            <button type="button" wire:click="$set('editingStaffId', null)" class="w-full py-4 text-slate-400 text-xs font-black hover:text-slate-600 transition-colors uppercase tracking-widest">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
