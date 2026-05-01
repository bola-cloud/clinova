<?php

use Livewire\Volt\Component;
use App\Services\AppointmentService;
use App\Models\Appointment;
use App\Models\DoctorNote;

new class extends Component
{
    public $selectedDate;
    public $dateOffset = 0;
    public $noteTitle = '';
    public $noteContent = '';
    public $noteDate = '';
    public $noteTime = '';
    public $showFinished = false;

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->noteDate = now()->format('Y-m-d');
        
        // Check for notes due today (date-only for backward compatibility and general overview)
        $dueToday = DoctorNote::where('doctor_id', auth()->id())
            ->where('is_completed', false)
            ->where('reminder_date', now()->format('Y-m-d'))
            ->get();
            
        if ($dueToday->isNotEmpty()) {
            $this->dispatch('due-notes-alert', notes: $dueToday->pluck('title')->toArray());
        }

        $this->noteTime = now()->addHour()->format('H:00');
    }

    public function toggleFinished()
    {
        $this->showFinished = !$this->showFinished;
    }

    public function setDate($date)
    {
        $this->selectedDate = $date;
        
        // Center the selected date in the 15-day window
        $diff = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($date)->startOfDay(), false);
        $this->dateOffset = $diff - 7;
    }

    public function nextDays()
    {
        $this->dateOffset += 7;
    }

    public function prevDays()
    {
        $this->dateOffset -= 7;
    }

    public function saveNote()
    {
        $this->validate([
            'noteTitle' => 'required|string|max:255',
            'noteContent' => 'required|string',
            'noteDate' => 'nullable|date',
            'noteTime' => 'nullable|string',
        ]);

        DoctorNote::create([
            'doctor_id' => auth()->id(),
            'title' => $this->noteTitle,
            'content' => $this->noteContent,
            'reminder_date' => $this->noteDate ?: null,
            'reminder_time' => $this->noteTime ?: null,
        ]);

        $this->reset(['noteTitle', 'noteContent']);
        $this->noteDate = now()->format('Y-m-d');
        
        $this->dispatch('notify', message: __('Note saved successfully!'));
    }

    public function deleteNote($id)
    {
        DoctorNote::where('id', $id)->where('doctor_id', auth()->id())->delete();
        $this->dispatch('notify', message: __('Note deleted!'));
    }

    public function toggleComplete($id)
    {
        $note = DoctorNote::where('id', $id)->where('doctor_id', auth()->id())->first();
        if ($note) {
            $note->update(['is_completed' => !$note->is_completed]);
        }
    }

    public function with()
    {
        $stats = app(AppointmentService::class)->getDoctorStats(auth()->id());
        $allAppointments = app(AppointmentService::class)->getAppointmentsForDoctor(auth()->id(), \Carbon\Carbon::parse($this->selectedDate)->startOfDay());
        
        return [
            'activeAppointments' => $allAppointments->filter(fn($a) => in_array($a->status, ['pending', 'checked-in'])),
            'finishedAppointments' => $allAppointments->filter(fn($a) => $a->status === 'seen'),
            'activeNotes' => DoctorNote::where('doctor_id', auth()->id())
                ->where('is_completed', false)
                ->orderBy('reminder_date', 'asc')
                ->get(),
            'completedNotes' => DoctorNote::where('doctor_id', auth()->id())
                ->where('is_completed', true)
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get(),
            'doctorStats' => $stats,
        ];
    }

    public function markAsSeen($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        app(AppointmentService::class)->updateStatus($appointment, 'seen');
        return redirect()->route('patients.show', $appointment->patient_id);
    }
};
?>

<div class="space-y-12 pb-24 font-['Cairo']" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <style>
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .animate-float { animation: float 5s ease-in-out infinite; }
        @keyframes sweep { 0% { transform: translateX(-100%); } 100% { transform: translateX(300%); } }
        .animate-sweep { animation: sweep 8s infinite linear; }
        @keyframes blob { 0%, 100% { transform: translate(0, 0) scale(1); } 33% { transform: translate(30px, -50px) scale(1.1); } 66% { transform: translate(-20px, 20px) scale(0.9); } }
        .animate-blob { animation: blob 10s infinite alternate; }
        .glass-card { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); }
    </style>

    <div class="w-full">
        <!-- New Top Statistics Row - Reordered for RTL Visual Logic -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Card 3: Weekly Stats (Right in RTL - Colored) - FIRST in DOM -->
            <div class="h-[240px] bg-gradient-to-br from-[#1A0B3B] via-[#4A26AB] to-[#6366F1] rounded-[3rem] p-10 text-white shadow-2xl shadow-indigo-900/30 relative overflow-hidden group hover:scale-[1.02] transition-all duration-500">
                <!-- Sparkline SVG -->
                <div class="absolute inset-x-0 bottom-0 h-2/3 opacity-30 pointer-events-none flex items-end">
                    <svg viewBox="0 0 400 100" class="w-full h-full preserve-3d" preserveAspectRatio="none">
                        <path d="M0,80 Q 50,20, 100,50 T 200,30 T 300,60 T 400,10" fill="none" stroke="white" stroke-width="4" stroke-linecap="round" class="animate-sweep" style="stroke-dasharray: 1000; stroke-dashoffset: 1000; animation: draw 5s forwards;"></path>
                    </svg>
                </div>
                
                <div class="flex items-start justify-between relative z-10 h-full">
                    <div class="w-16 h-16 rounded-[1.5rem] bg-white/10 flex items-center justify-center border border-white/20 backdrop-blur-md shrink-0">
                        <svg class="w-8 h-8 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div class="text-right flex-1 pr-6 flex flex-col justify-between h-full pb-2">
                        <p class="text-indigo-100 text-base md:text-xl font-black tracking-tight">{{ __("Daily Appointments") }}</p>
                        <div class="flex items-baseline justify-end mt-auto">
                            <span class="text-[4rem] md:text-[5rem] leading-none font-black tracking-tighter">{{ $doctorStats['todayTotal'] }}</span>
                        </div>
                    </div>
                </div>
                <!-- Animated Blobs -->
                <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-white/5 blur-3xl animate-blob"></div>
                <div class="absolute -left-10 -bottom-10 w-48 h-48 rounded-full bg-indigo-400/10 blur-3xl animate-blob" style="animation-delay: 2s;"></div>
            </div>

            <!-- Card 2: Patients Remaining (Middle) -->
            <div class="h-[240px] bg-white rounded-[3rem] p-10 shadow-xl shadow-gray-100/50 border border-gray-50 flex flex-col justify-between group hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-16 h-16 rounded-[1.5rem] bg-emerald-50 flex items-center justify-center border border-emerald-100 shrink-0">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-right flex flex-col justify-between h-full">
                        <p class="text-gray-900 text-base md:text-xl font-black tracking-tight mb-2">{{ __("Patients Remaining Today") }}</p>
                        <span class="text-[4rem] md:text-[5rem] leading-none font-black text-slate-900 tracking-tighter mt-auto">{{ $doctorStats['remainingToday'] }}</span>
                    </div>
                </div>
                <p class="text-xs md:text-sm text-gray-400 font-bold mt-auto">{{ __("No more patients yet, enjoy your time!") }}</p>
            </div>

            <!-- Card 1: Performance (Left in RTL) -->
            <div class="h-[240px] bg-white rounded-[3rem] p-10 shadow-xl shadow-gray-100/50 border border-gray-50 flex flex-col justify-between group hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                <div class="flex items-start justify-between mb-8">
                    <div class="w-16 h-16 rounded-[1.5rem] bg-emerald-50 flex items-center justify-center border border-emerald-100 shrink-0">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="text-right flex flex-col justify-between h-full">
                        <p class="text-gray-900 text-base md:text-xl font-black tracking-tight mb-2">{{ __("Performance Yesterday") }}</p>
                        <span class="text-[4rem] md:text-[5rem] leading-none font-black text-slate-900 tracking-tighter mt-auto">{{ $doctorStats['yesterdayPerformance'] }}%</span>
                    </div>
                </div>
                <div class="space-y-3 mt-auto">
                    <div class="w-full h-3 md:h-4 bg-gray-100 rounded-full overflow-hidden border border-gray-50">
                        <div class="h-full bg-[#10b981] rounded-full transition-all duration-1000" style="width: {{ $doctorStats['yesterdayPerformance'] }}%"></div>
                    </div>
                    <p class="text-xs md:text-sm text-gray-400 font-bold">{{ $doctorStats['yesterdayCompleted'] }} {{ __("out of tasks completed") }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Section: Doctor Profile and Schedule - Reordered for RTL (RHS First) -->
    <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-10 items-start mt-6">
        <!-- Schedule Section (RHS in RTL - FIRST in DOM) -->
        <div class="lg:col-span-8 space-y-6 pt-12">
            <!-- Header -->
            <div class="flex items-center justify-between px-2">
                <h2 class="text-5xl font-black text-slate-900 tracking-tighter">{{ __("Today at a Glance") }}</h2>
            </div>

            <!-- Central Card (Contains Day Selector & Status/List) -->
            <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-xl shadow-gray-100/50 border border-gray-100 relative overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-bold text-slate-900 opacity-0">{{ __("Day") }}</h3> <!-- Spacer to align tools to left in RTL -->
                    <div class="flex items-center gap-3 flex-row-reverse">
                        <button class="text-indigo-600 font-bold text-sm flex items-center gap-2 bg-indigo-50/50 hover:bg-indigo-100 px-4 py-2.5 rounded-2xl transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            {{ __("Refresh List") }}
                        </button>
                        <div class="flex items-center gap-1.5 ml-auto">
                            {{-- Calendar date picker --}}
                            <div class="relative">
                                <button onclick="document.getElementById('dashboardDatePicker').showPicker()" class="w-10 h-10 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-indigo-500 transition-colors shrink-0 cursor-pointer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></button>
                                <input type="date" id="dashboardDatePicker" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer" value="{{ $selectedDate }}" wire:change="setDate($event.target.value)" />
                            </div>
                            {{-- Previous day --}}
                            <button wire:click="setDate('{{ \Carbon\Carbon::parse($selectedDate)->subDay()->format('Y-m-d') }}')" class="w-10 h-10 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-indigo-500 transition-colors shrink-0 cursor-pointer"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg></button>
                            <button class="px-5 py-2.5 rounded-2xl bg-indigo-50 text-indigo-600 font-black text-xs shrink-0 tracking-tight">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</button>
                        </div>
                    </div>
                </div>

                <!-- Horizontal Scroller with Pagination -->
                <div class="relative group/scroller">
                    <!-- Navigation Arrows -->
                    <button wire:click="prevDays" class="absolute -left-6 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white border border-gray-100 shadow-lg flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:scale-110 transition-all z-20 opacity-0 group-hover/scroller:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                    </button>
                    
                    <div class="flex gap-4 overflow-x-auto pb-8 scrollbar-hide border-b border-gray-100 mb-8 max-w-full" dir="rtl">
                        @php 
                            $today = now()->startOfDay();
                        @endphp
                        @foreach(range($dateOffset, $dateOffset + 14) as $i)
                            @php 
                                $dateObj = $today->copy()->addDays($i);
                                $dateStr = $dateObj->format('Y-m-d');
                                $dayNum = $dateObj->format('d');
                                $dayName = $dateObj->translatedFormat('l');
                                $isSelected = $selectedDate === $dateStr;
                            @endphp
                            <div class="flex-shrink-0 flex flex-col items-center gap-2 cursor-pointer" wire:click="setDate('{{ $dateStr }}')">
                                <button class="w-16 h-16 rounded-2xl border-2 {{ $isSelected ? 'bg-slate-900 border-slate-900 text-white shadow-xl shadow-slate-200' : 'bg-white border-gray-100 text-slate-800' }} hover:border-indigo-400 hover:-translate-y-1 transition-all duration-300 flex flex-col items-center justify-center group pointer-events-none">
                                    <span class="text-2xl font-black">{{ $dayNum }}</span>
                                </button>
                                <span class="text-xs font-bold {{ $isSelected ? 'text-indigo-600' : 'text-gray-400' }} whitespace-nowrap tracking-wide">{{ $dayName }}</span>
                            </div>
                        @endforeach
                    </div>

                    <button wire:click="nextDays" class="absolute -right-6 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white border border-gray-100 shadow-lg flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:scale-110 transition-all z-20 opacity-0 group-hover/scroller:opacity-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>

                <!-- Active Appointments List -->
                @if($activeAppointments->isEmpty() && $finishedAppointments->isEmpty())
                <div class="flex items-center justify-center md:justify-start gap-4 py-8 bg-emerald-50/50 rounded-3xl px-8 border border-emerald-50/50">
                    <span class="text-lg font-black text-slate-900">{{ __("All Clear! No appointments scheduled for this clinic today.") }}</span>
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 shadow-sm border border-emerald-200">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                </div>
                @else
                <div class="overflow-x-auto overflow-y-auto max-h-[500px] -mx-4 px-4 scrollbar-hide">
                    <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                        <thead class="text-gray-400 text-[10px] font-black uppercase tracking-[0.2em] border-b border-gray-50">
                            <tr>
                                <th class="px-2 py-6 font-black text-right w-10">#</th>
                                <th class="px-6 py-6 font-black text-right min-w-[250px] w-full">{{ __('Patient') }}</th>
                                <th class="px-6 py-6 font-black text-center min-w-[160px]">{{ __('Type') }}</th>
                                <th class="px-6 py-6 font-black text-center min-w-[120px]">{{ __('Appointment') }}</th>
                                <th class="px-6 py-6 font-black text-center min-w-[140px]">{{ __('Status') }}</th>
                                <th class="px-6 py-6 font-black text-left">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($activeAppointments as $appointment)
                            <tr class="group hover:bg-slate-50/50 transition-all duration-300">
                                <td class="px-2 py-8 text-right">
                                    <span class="text-xs font-black text-slate-400">{{ $loop->iteration }}</span>
                                </td>
                                <td class="px-6 py-8">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-100 to-purple-100 flex items-center justify-center font-black text-indigo-600 text-sm shadow-sm border border-white">
                                            {{ substr($appointment->patient->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('patients.show', $appointment->patient_id) }}" class="font-black text-slate-900 group-hover:text-indigo-600 transition-colors block text-lg tracking-tight">
                                                {{ $appointment->patient->name }}
                                            </a>
                                            <span class="text-xs text-gray-400 font-bold">{{ $appointment->patient->phone }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-8 text-center">
                                    @if($appointment->type === 'checkup')
                                        <span class="px-3 py-1.5 bg-purple-100 text-purple-700 text-xs rounded-full font-black uppercase tracking-widest border border-purple-200">{{ __('Consultation Case') }}</span>
                                    @else
                                        <span class="px-3 py-1.5 bg-amber-100 text-amber-700 text-xs rounded-full font-black uppercase tracking-widest border border-amber-200">{{ __('Follow-up Case') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-8 text-center">
                                    <span class="text-lg font-black text-slate-800 tracking-tighter">{{ $appointment->scheduled_at->format('H:i') }}</span>
                                </td>
                                <td class="px-6 py-8 text-center">
                                    @if($appointment->status === 'checked-in')
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-[0_0_10px_rgba(59,130,246,0.5)] animate-pulse"></div>
                                            <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">{{ __('Wait') }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-2.5 h-2.5 rounded-full bg-slate-300"></div>
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('Pending') }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-8 text-left">
                                    @if($appointment->status === 'checked-in')
                                        @if($appointment->type === 'checkup')
                                            <a href="{{ route('appointments.visit', $appointment->id) }}" class="px-8 py-3 bg-slate-900 text-white rounded-full text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 hover:bg-slate-800 hover:-translate-y-0.5 transition-all inline-block">{{ __('Start') }}</a>
                                        @else
                                            <button wire:click="markAsSeen({{ $appointment->id }})" class="px-8 py-3 bg-indigo-600 text-white rounded-full text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all inline-block">{{ __('Start') }}</button>
                                        @endif
                                    @else
                                        <a href="{{ route('patients.show', $appointment->patient_id) }}" class="w-10 h-10 rounded-full border border-gray-100 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Finished Appointments Collapsible -->
                @if($finishedAppointments->isNotEmpty())
                <div class="mt-8 pt-8 border-t border-gray-50">
                    <button wire:click="toggleFinished" class="flex items-center justify-between w-full px-6 py-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-emerald-500 border border-emerald-100 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="text-right">
                                <h4 class="font-black text-slate-900 text-sm tracking-tight">{{ __("Completed Appointments") }}</h4>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $finishedAppointments->count() }} {{ __("Appointments completed today") }}</p>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white text-gray-400 group-hover:bg-indigo-50 transition-colors {{ $showFinished ? 'rotate-180' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </button>

                    @if($showFinished)
                    <div class="mt-4 overflow-x-auto animate-slide-in-top">
                        <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                            <tbody class="divide-y divide-gray-50">
                                @foreach($finishedAppointments as $appointment)
                                <tr class="group hover:bg-slate-50/50 transition-all duration-300 opacity-60">
                                    <td class="px-6 py-6 text-right w-16">
                                        <span class="text-[10px] font-black text-slate-400">#{{ $loop->iteration }}</span>
                                    </td>
                                    <td class="px-6 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center font-black text-emerald-600 text-xs border border-white">
                                                {{ substr($appointment->patient->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('patients.show', $appointment->patient_id) }}" class="font-bold text-slate-800 text-sm tracking-tight">
                                                    {{ $appointment->patient->name }}
                                                </a>
                                                <span class="text-[10px] text-gray-400 font-bold block">{{ $appointment->patient->phone }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-center">
                                        <span class="text-sm font-black text-slate-600 tracking-tighter">{{ $appointment->scheduled_at->format('H:i') }}</span>
                                    </td>
                                    <td class="px-6 py-6 text-left">
                                        <a href="{{ route('patients.show', $appointment->patient_id) }}" class="w-8 h-8 rounded-full border border-gray-100 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
                @endif
                @endif
            </div>

            <!-- Important Reminders Section -->
            <div class="space-y-6 pt-6 px-2">
                <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ __("Important Reminders") }}</h4>
                <div class="space-y-4">
                    @forelse($activeNotes as $note)
                        <div x-data="{ open: false }" class="bg-white rounded-[2rem] shadow-sm shadow-gray-100/30 border border-gray-100/50 overflow-hidden group transition-all duration-300">
                            <div @click="open = !open" class="p-6 flex items-center justify-between cursor-pointer hover:bg-slate-50/50 transition-colors">
                                <div class="flex items-center gap-5">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 border border-amber-100/50 shrink-0 group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-black text-slate-800 text-sm tracking-tight">{{ $note->title }}</span>
                                        @if($note->reminder_date)
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $note->reminder_date->translatedFormat('d F Y') }}</span>
                                                @if($note->reminder_time)
                                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                                    <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider">{{ \Carbon\Carbon::parse($note->reminder_time)->format('H:i') }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button wire:click.stop="toggleComplete({{ $note->id }})" class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all shadow-sm">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-gray-50 text-gray-400 group-hover:bg-indigo-50 transition-colors" :class="open ? 'rotate-180 text-indigo-600' : ''">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                            </div>
                            <div x-show="open" x-collapse x-cloak>
                                <div class="px-6 pb-6 pt-2 text-sm text-gray-600 font-medium leading-relaxed border-t border-gray-50 bg-slate-50/30">
                                    {{ $note->content }}
                                    <div class="mt-4 flex justify-end">
                                        <button wire:click="deleteNote({{ $note->id }})" class="text-[10px] font-black text-red-500 uppercase tracking-widest hover:text-red-700 transition-colors">
                                            {{ __('Delete Note') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center bg-slate-50/50 rounded-[2rem] border-2 border-dashed border-gray-100">
                            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-gray-200 mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <span class="text-sm font-bold text-gray-400">{{ __("No notes currently") }}</span>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($completedNotes->isNotEmpty())
            <!-- Completed Section -->
            <div class="space-y-6 pt-10 px-2 opacity-70">
                <div class="flex items-center gap-3">
                    <div class="h-px bg-emerald-100 flex-1"></div>
                    <h4 class="text-sm font-black text-emerald-600 tracking-[0.2em] uppercase">{{ __("Completed Tasks") }}</h4>
                    <div class="h-px bg-emerald-100 flex-1"></div>
                </div>
                <div class="space-y-3">
                    @foreach($completedNotes as $note)
                        <div x-data="{ open: false }" class="bg-emerald-50/30 rounded-2xl border border-emerald-100/50 overflow-hidden group transition-all duration-300">
                            <div @click="open = !open" class="px-5 py-4 flex items-center justify-between cursor-pointer">
                                <div class="flex items-center gap-4">
                                    <button wire:click.stop="toggleComplete({{ $note->id }})" class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 text-xs line-through opacity-60">{{ $note->title }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button wire:click.stop="deleteNote({{ $note->id }})" class="p-2 text-gray-300 hover:text-red-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                            <div x-show="open" x-collapse x-cloak class="px-5 pb-4 text-xs text-gray-500 font-medium">
                                {{ $note->content }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Doctor Profile Image Area & Notes (LHS in RTL) -->
        <div class="lg:col-span-4 flex flex-col items-center justify-start relative pt-16 space-y-12">
            
            <!-- Doctor Spotlight -->
            <div class="relative group mt-8">
                <!-- Outer Glow -->
                <div class="absolute -inset-2 bg-gradient-to-tr from-indigo-500 to-purple-500 rounded-full opacity-20 blur-2xl group-hover:opacity-40 transition-opacity duration-700 animate-pulse"></div>
                <!-- Shrunken Avatar Circle -->
                <div class="w-56 h-56 md:w-64 md:h-64 rounded-full border-[8px] border-white shadow-2xl shadow-indigo-900/10 overflow-hidden bg-slate-50 transition-transform duration-700 hover:scale-[1.02] relative z-10 mx-auto">
                    @if(auth()->user()->profile_image)
                        <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" alt="Doctor" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-tr from-indigo-50 to-purple-50">
                            <svg class="w-24 h-24 text-indigo-200" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                    @endif
                </div>

                <!-- Floating Badge 'Start Day' -->
                <div class="absolute -top-4 right-1/2 translate-x-1/2 bg-white px-6 py-2 rounded-full shadow-lg shadow-gray-200/50 border border-gray-100 flex items-center justify-center z-20 animate-float active:scale-95 cursor-default whitespace-nowrap">
                    <span class="text-xs font-black text-slate-800 tracking-tight">{{ __("Start your day now") }}</span>
                </div>
            </div>

            <!-- New: Doctor Quick Notes / Notification Module -->
            <div class="w-full bg-white rounded-[2rem] p-6 shadow-xl shadow-gray-100/50 border border-gray-100 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-b from-indigo-50/50 to-transparent pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                <div class="relative z-10 space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-gray-50">
                        <h4 class="text-lg font-black text-slate-900">{{ __("Quick Notes") }}</h4>
                        <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                    </div>
                    
                    <div class="space-y-3 pt-2">
                        <input type="text" wire:model="noteTitle" class="w-full bg-slate-50 border-0 border-b-2 border-transparent focus:border-indigo-500 focus:ring-0 rounded-xl px-4 py-3 text-sm font-black text-slate-800 placeholder:text-gray-400 transition-colors" placeholder="{{ __('Note Title...') }}">
                        
                        <textarea rows="3" wire:model="noteContent" class="w-full bg-slate-50 border-0 border-b-2 border-transparent focus:border-indigo-500 focus:ring-0 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 resize-none transition-colors" placeholder="{{ __('Add a reminder or note for a specific day...') }}"></textarea>
                        
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2 flex-1 min-w-[200px]">
                                <input type="date" wire:model="noteDate" class="bg-slate-50 border-none rounded-xl text-[11px] font-bold text-slate-600 px-3 py-2 flex-1 h-10 outline-none focus:ring-2 focus:ring-indigo-500/20" />
                                <input type="time" wire:model="noteTime" class="bg-slate-50 border-none rounded-xl text-[11px] font-bold text-slate-600 px-3 py-2 w-24 h-10 outline-none focus:ring-2 focus:ring-indigo-500/20" />
                            </div>
                            
                            <button wire:click="saveNote" class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-lg hover:bg-indigo-600 hover:-translate-y-0.5 transition-all outline-none shrink-0" title="{{ __('Save Note') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
        </div>
    </div>
</div>