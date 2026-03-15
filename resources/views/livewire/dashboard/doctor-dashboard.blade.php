<?php

use Livewire\Volt\Component;
use App\Services\AppointmentService;
use App\Models\Appointment;

new class extends Component
{
    public function with()
    {
        return [
            'appointments' => app(AppointmentService::class)->getAppointmentsForDoctor(auth()->id(), now())
        ];
    }

    public function markAsSeen($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        app(AppointmentService::class)->updateStatus($appointment, 'seen');
    }
};
?>

<div class="space-y-6" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stats -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-gray-500 text-sm mb-1">{{ __("Total Cases Today") }}</p>
            <h3 class="text-2xl font-bold">{{ $appointments->count() }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-gray-500 text-sm mb-1">{{ __("Seen") }}</p>
            <h3 class="text-2xl font-bold text-green-600">{{ $appointments->where('status', 'seen')->count() }}</h3>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-gray-500 text-sm mb-1">{{ __("Waiting for Exam") }}</p>
            <h3 class="text-2xl font-bold text-purple-600">{{ $appointments->where('status', 'checked-in')->count() }}</h3>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-lg">{{ __('Queue List') }}</h3>
            <button wire:click="$refresh" class="text-purple-600 text-sm font-medium">{{ __('Refresh List') }}</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                <thead class="bg-gray-50 text-gray-500 text-sm">
                    <tr>
                        <th class="px-6 py-4 font-medium">{{ __('Patient') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Appointment') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Status') }}</th>
                        <th class="px-6 py-4 font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($appointments as $appointment)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 font-medium">
                            <a href="{{ route('patients.show', $appointment->patient_id) }}" class="hover:text-purple-600 transition-colors block">
                                {{ $appointment->patient->name }}
                            </a>
                            <span class="text-xs text-gray-400">{{ $appointment->patient->phone }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-600 font-bold">{{ $appointment->scheduled_at->format('H:i') }}</div>
                            @if(is_array($appointment->audit_log) && count($appointment->audit_log) > 0)
                                @php
                                    $logs = $appointment->audit_log;
                                    $lastLog = is_array($logs) ? end($logs) : null;
                                @endphp
                                @if($lastLog['action'] === 'modifed')
                                    <div class="text-[10px] text-amber-600 mt-1 flex items-center gap-1 group relative w-max cursor-help">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ __('Modified by') }} {{ explode(' ', trim($lastLog['by_user_name']))[0] }}
                                        
                                        <!-- Tooltip -->
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-48 bg-gray-900 text-white text-[10px] rounded-lg p-2 shadow-xl z-10 before:content-[''] before:absolute before:top-full before:left-1/2 before:-translate-x-1/2 before:border-4 before:border-transparent before:border-t-gray-900">
                                            {{ __('Changed at:') }} {{ \Carbon\Carbon::parse($lastLog['timestamp'])->format('H:i') }}<br>
                                            {{ __('From:') }} {{ \Carbon\Carbon::parse($lastLog['changes']['time_from'])->format('H:i') }}
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($appointment->status === 'seen')
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full">{{ __('Seen') }}</span>
                            @elseif($appointment->status === 'checked-in')
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">{{ __('Checked In') }}</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">{{ __('Pending') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($appointment->status === 'checked-in')
                            <a href="{{ route('appointments.visit', $appointment->id) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-bold shadow-md shadow-purple-100 inline-block">{{ __('Start Visit') }}</a>
                            @elseif($appointment->status === 'pending')
                            <span class="text-gray-400 text-sm italic">{{ __('Waiting for Preparation') }}</span>
                            @else
                            <a href="{{ route('patients.show', $appointment->patient_id) }}" class="text-purple-600 hover:underline text-sm font-bold">{{ __('Patient History') }}</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400">{{ __('No appointments for today yet.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>