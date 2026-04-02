<div class="space-y-6">
    <!-- Header & Filters -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">{{ __('System Revenue Statistics') }}</h2>
            <p class="text-gray-500 font-medium italic">{{ __('Detailed tracking of all subscription payments and system income.') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <select wire:model.live="year" class="bg-white border-gray-200 rounded-2xl px-4 py-2.5 text-sm font-bold focus:ring-4 focus:ring-purple-500/10 shadow-sm">
                @foreach(range(date('Y'), 2024) as $i)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endforeach
            </select>
            <select wire:model.live="month" class="bg-white border-gray-200 rounded-2xl px-4 py-2.5 text-sm font-bold focus:ring-4 focus:ring-purple-500/10 shadow-sm">
                <option value="all">{{ __('All Months') }}</option>
                @foreach(range(1, 12) as $m)
                    <option value="{{ sprintf('%02d', $m) }}">{{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endforeach
            </select>
            <select wire:model.live="selectedPlan" class="bg-white border-gray-200 rounded-2xl px-4 py-2.5 text-sm font-bold focus:ring-4 focus:ring-purple-500/10 shadow-sm">
                <option value="all">{{ __('All Plans') }}</option>
                <option value="trial">{{ __('Trial') }}</option>
                <option value="monthly">{{ __('Monthly') }}</option>
                <option value="yearly">{{ __('Yearly') }}</option>
            </select>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm group hover:border-emerald-200 transition-all">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Collected Revenue') }}</p>
            <h4 class="text-3xl font-black text-slate-900 leading-tight" dir="ltr">{{ number_format($stats['total_revenue'], 2) }} <span class="text-xs text-emerald-600 ml-1 uppercase">EGP</span></h4>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm group hover:border-amber-200 transition-all">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Pending Revenue') }}</p>
            <h4 class="text-3xl font-black text-slate-900 leading-tight" dir="ltr">{{ number_format($stats['pending_revenue'], 2) }} <span class="text-xs text-amber-600 ml-1 uppercase">EGP</span></h4>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm group hover:border-purple-200 transition-all">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Active Subs') }}</p>
            <h4 class="text-3xl font-black text-slate-900 leading-tight">{{ number_format($stats['total_count']) }}</h4>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm group hover:border-blue-200 transition-all">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ __('Collection Rate') }}</p>
            <h4 class="text-3xl font-black text-slate-900 leading-tight">
                {{ $stats['total_count'] > 0 ? round(($stats['paid_count'] / $stats['total_count']) * 100) : 0 }}%
            </h4>
        </div>
    </div>

    <!-- Monthly Chart Placeholder -->
    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
        <h3 class="text-xl font-black text-slate-900 mb-8">{{ __('Revenue Trends') }} ({{ $year }})</h3>
        <div class="h-64 flex items-end gap-2 md:gap-4 px-4" dir="ltr">
            @php
                $maxVal = max($chartData) ?: 1;
            @endphp
            @foreach($chartData as $index => $value)
                <div class="flex-1 flex flex-col items-center gap-3 group relative">
                    <div class="w-full bg-slate-50 rounded-t-xl group-hover:bg-purple-100 transition-all overflow-hidden flex flex-col justify-end" style="height: 100%">
                        <div class="bg-gradient-to-t from-purple-600 to-indigo-500 rounded-t-xl w-full transition-all duration-700 animate-slide-up" 
                             style="height: {{ ($value / $maxVal) * 100 }}%">
                        </div>
                    </div>
                    <span class="text-[10px] font-black text-gray-400">{{ Carbon\Carbon::create()->month($index + 1)->format('M') }}</span>
                    
                    <!-- Tooltip -->
                    <div class="absolute -top-12 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 text-white text-[10px] font-black py-1 px-3 rounded-lg pointer-events-none whitespace-nowrap z-10 shadow-xl">
                        {{ number_format($value, 0) }} EGP
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- History Table -->
    <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ __('Recent Subscription Activity') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                <thead class="bg-slate-50/50 text-gray-500 text-xs font-black uppercase tracking-widest">
                    <tr>
                        <th class="px-8 py-5">{{ __('Doctor / Clinic') }}</th>
                        <th class="px-8 py-5">{{ __('Plan') }}</th>
                        <th class="px-8 py-5">{{ __('Period') }}</th>
                        <th class="px-8 py-5 text-center">{{ __('Status') }}</th>
                        <th class="px-8 py-5 text-left">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($subscriptions as $sub)
                    <tr class="hover:bg-purple-50/20 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center font-black text-sm uppercase">
                                    {{ mb_substr($sub->user->name, 0, 1) }}
                                </div>
                                <span class="font-black text-slate-900">{{ $sub->user->name }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-[10px] font-black rounded-full uppercase tracking-widest group-hover:bg-white transition-colors">
                                {{ __($sub->plan_name) }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-slate-900">{{ $sub->start_date?->format('Y-m-d') }}</span>
                                <span class="text-[10px] text-gray-400 font-medium">to {{ $sub->end_date?->format('Y-m-d') }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            @if($sub->is_paid)
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full uppercase tracking-widest">
                                    {{ __('Paid Status') }}
                                </span>
                            @else
                                <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black rounded-full uppercase tracking-widest">
                                    {{ __('Pending Status') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-left font-black text-slate-900" dir="ltr">
                            {{ number_format($sub->amount, 2) }} EGP
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center text-gray-400 font-black italic">{{ __('No revenue records found for this selection.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .animate-slide-up { animation: slideUp 1s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
    </style>
</div>
