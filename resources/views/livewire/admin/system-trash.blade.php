<div class="space-y-3" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between gap-3 items-center bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center shadow-inner">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </div>
            <div>
                <h1 class="text-lg font-black text-gray-900 leading-tight">{{ __('System Trash (Soft Deleted)') }}</h1>
                <p class="text-[10px] text-gray-500 font-medium">{{ __('Manage and permanently delete or restore soft-deleted records.') }}</p>
            </div>
        </div>
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-3">
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-3' : 'left-0 pl-3' }} flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search...') }}" class="w-full bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-rose-500 py-2 {{ app()->getLocale() === 'ar' ? 'pr-10' : 'pl-10' }} text-sm transition-shadow">
            </div>
            @if(auth()->user()->isDoctor())
                <button type="button" wire:click="downloadBackup" wire:loading.attr="disabled" class="px-4 py-2 bg-emerald-100 border border-emerald-200 text-emerald-700 hover:bg-emerald-600 hover:text-white font-bold rounded-xl shadow-sm transition-all text-xs flex items-center justify-center gap-2 w-full md:w-auto whitespace-nowrap disabled:opacity-55 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    {{ __('Export Backup Data') }}
                </button>
            @endif
        </div>
    </div>

    @if (session()->has('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center gap-3 shadow-sm animate-fade-in-down">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Tabs -->
    <div class="flex flex-wrap gap-2">
        @php
            $tabs = [
                'patients' => __('Patients'),
                'appointments' => __('Appointments'),
                'visits' => __('Visits'),
                'files' => __('Medical Files')
            ];
            if (auth()->user()->isAdmin()) {
                $tabs['doctors'] = __('Doctors');
            }
        @endphp
        @foreach($tabs as $tab => $label)
            <button wire:click="$set('currentTab', '{{ $tab }}')" class="px-4 py-2 rounded-xl font-bold text-xs transition-all flex items-center gap-2 {{ $currentTab === $tab ? 'bg-rose-600 text-white shadow-lg shadow-rose-200' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                {{ $label }}
                <span class="px-2 py-0.5 rounded-md text-[9px] {{ $currentTab === $tab ? 'bg-rose-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                    {{ $stats[$tab] ?? 0 }}
                </span>
            </button>
        @endforeach
    </div>

    <!-- Table content -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-500">
                    <tr>
                        <th class="px-4 py-3 text-xs font-bold">{{ __('Record Info') }}</th>
                        <th class="px-4 py-3 text-xs font-bold">{{ __('Deleted At') }}</th>
                        <th class="px-4 py-3 text-xs font-bold text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($records as $record)
                    <tr class="hover:bg-rose-50/30 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-bold text-sm text-gray-900">
                                @if($currentTab === 'patients')
                                    {{ $record->name }} ({{ $record->phone }})
                                @elseif($currentTab === 'appointments')
                                    {{ $record->patient?->name ?? __('Unknown Patient') }} - {{ \Carbon\Carbon::parse($record->scheduled_at)->format('Y-m-d H:i') }}
                                @elseif($currentTab === 'visits')
                                    {{ $record->patient?->name ?? __('Unknown Patient') }} - {{ \Carbon\Carbon::parse($record->created_at)->format('Y-m-d') }}
                                @elseif($currentTab === 'files')
                                    {{ $record->file_name }} ({{ $record->patient?->name ?? __('Unknown Patient') }})
                                @elseif($currentTab === 'doctors')
                                    {{ __('Dr.') }} {{ $record->name }} ({{ $record->email }})
                                @endif
                            </div>
                            <div class="text-[10px] text-gray-500 mt-0.5">ID: #{{ $record->id }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs font-bold text-rose-600">
                            {{ $record->deleted_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="restoreRecord('{{ rtrim($currentTab, 's') }}', {{ $record->id }})" class="inline-flex items-center justify-center px-3 py-1.5 bg-emerald-100 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-lg transition-all shadow-sm border border-emerald-200 font-bold text-[10px] gap-1" title="{{ __('Restore') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                    {{ __('Restore') }}
                                </button>
                                @if(auth()->user()->isAdmin())
                                <button wire:click="forceDeleteRecord('{{ rtrim($currentTab, 's') }}', {{ $record->id }})" wire:confirm="{{ __('Are you sure you want to PERMANENTLY delete this record? This action cannot be undone.') }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-rose-100 text-rose-700 hover:bg-rose-600 hover:text-white rounded-lg transition-all shadow-sm border border-rose-200 font-bold text-[10px] gap-1" title="{{ __('Permanent Delete') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    {{ __('Force Delete') }}
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-xs text-gray-500 font-medium">
                            {{ __('No deleted records found in this category.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($records->hasPages())
        <div class="p-3 border-t border-gray-50 bg-gray-50/50">
            {{ $records->links(data: ['scrollTo' => false]) }}
        </div>
        @endif
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
