<?php

use function Livewire\Volt\{state, mount};
use App\Models\DoctorNote;
use Carbon\Carbon;

state(['lastNotifiedNoteId' => null]);

$checkReminders = function () {
    $user = auth()->user();
    if (!$user) return;

    // Get the doctor ID (if user is doctor or secretary)
    $doctorId = $user->role === 'doctor' ? $user->id : ($user->isSecretary() ? $user->assigned_doctor_id : null);
    
    if (!$doctorId) return;

    $now = Carbon::now();
    
    // Find a note that is due now (today, reminder_time <= current time, not completed)
    $dueNote = DoctorNote::where('doctor_id', $doctorId)
        ->where('is_completed', false)
        ->whereDate('reminder_date', $now->toDateString())
        ->whereNotNull('reminder_time')
        ->whereTime('reminder_time', '<=', $now->toTimeString())
        ->where('id', '!=', $this->lastNotifiedNoteId)
        ->orderBy('reminder_time', 'desc')
        ->first();

    if ($dueNote) {
        $this->lastNotifiedNoteId = $dueNote->id;
        
        $this->dispatch('reminder-alarm', 
            title: $dueNote->title ?: __('Reminder'),
            content: $dueNote->content ?: ''
        );
    }
};

?>

<div wire:poll.30s="checkReminders">
    <!-- Floating Notifications System (Global) -->
    <div x-data="{ 
            notifications: [], 
            add(msg, subtitle = '', type = 'info') {
                const id = Date.now();
                this.notifications.push({ id, msg, subtitle, type });
                if (type === 'alarm') {
                    this.playAlarm();
                    setTimeout(() => this.remove(id), 15000); 
                } else {
                    setTimeout(() => this.remove(id), 6000);
                }
            },
            playAlarm() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    gain.gain.setValueAtTime(0, ctx.currentTime);
                    gain.gain.linearRampToValueAtTime(0.1, ctx.currentTime + 0.01);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.5);
                } catch(e) { console.error('Audio fail', e); }
            },
            remove(id) {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }
         }" 
         @reminder-alarm.window="add('{{ __('Reminder Alert!') }}', $event.detail.title + ': ' + $event.detail.content, 'alarm')"
         @notify.window="add($event.detail.message, $event.detail.subtitle || '', $event.detail.type || 'info')"
         class="fixed bottom-10 left-10 z-[100] flex flex-col gap-4 pointer-events-none w-full max-w-sm"
    >
        <template x-for="n in notifications" :key="n.id">
            <div x-show="true" 
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 -translate-x-12 scale-90"
                 x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-x-12 scale-90"
                 :class="n.type === 'alarm' ? 'bg-indigo-600 border-indigo-400 ring-4 ring-indigo-500/20 text-white' : 'bg-slate-900 border-slate-800 text-white'"
                 class="pointer-events-auto shadow-2xl shadow-slate-200 rounded-[2rem] p-6 border flex items-center gap-5 group hover:-translate-y-1 transition-all"
            >
                <div :class="n.type === 'alarm' ? 'bg-white/20 text-white' : 'bg-indigo-500/10 text-indigo-400'"
                     class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 border border-white/10 group-hover:bg-indigo-500 group-hover:text-white transition-colors"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <div class="flex flex-col">
                    <h5 class="font-black text-lg leading-tight" x-text="n.msg"></h5>
                    <p class="text-xs font-bold opacity-80 mt-1" x-text="n.subtitle"></p>
                </div>
                <button @click="remove(n.id)" class="ml-auto p-2 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white/10 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </template>
    </div>
</div>
