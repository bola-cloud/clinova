<?php

namespace App\Livewire\Doctor;

use Livewire\Component;
use App\Models\DoctorSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ScheduleSettings extends Component
{
    public $booking_slug;
    public $is_booking_active;

    public $schedules = [];
    
    public $days = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public function mount()
    {
        $user = Auth::user();
        $doctorUser = $user->isSecretary() ? \App\Models\User::find($user->doctor_id) : $user;

        $this->booking_slug = $doctorUser->booking_slug;
        $this->is_booking_active = $doctorUser->is_booking_active;

        $existingSchedules = $doctorUser->schedules->keyBy('day_of_week');

        foreach ($this->days as $index => $name) {
            if ($existingSchedules->has($index)) {
                $sch = $existingSchedules[$index];
                $this->schedules[$index] = [
                    'is_working_day' => (bool) $sch->is_working_day,
                    'start_time' => substr($sch->start_time, 0, 5),
                    'end_time' => substr($sch->end_time, 0, 5),
                    'slot_duration_minutes' => $sch->slot_duration_minutes,
                ];
            } else {
                $this->schedules[$index] = [
                    'is_working_day' => false,
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                    'slot_duration_minutes' => 15,
                ];
            }
        }
    }

    public function saveSettings()
    {
        $user = Auth::user();
        $doctorUser = $user->isSecretary() ? \App\Models\User::find($user->doctor_id) : $user;
        
        $this->validate([
            'booking_slug' => [
                'nullable',
                'string',
                'alpha_dash',
                'max:255',
                Rule::unique('users')->ignore($doctorUser->id),
            ],
            'is_booking_active' => 'boolean',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i',
            'schedules.*.slot_duration_minutes' => 'required|integer|min:5|max:120',
        ]);

        $doctorUser->update([
            'booking_slug' => $this->booking_slug,
            'is_booking_active' => $this->is_booking_active,
        ]);

        foreach ($this->schedules as $day => $data) {
            DoctorSchedule::updateOrCreate(
                ['doctor_id' => $doctorUser->id, 'day_of_week' => $day],
                [
                    'is_working_day' => $data['is_working_day'] ?? false,
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'slot_duration_minutes' => $data['slot_duration_minutes'],
                ]
            );
        }

        session()->flash('message', __('Schedule settings saved successfully.'));
    }

    public function render()
    {
        return view('livewire.doctor.schedule-settings')
            ->layout('layouts.clinic', ['title' => __('Schedule Settings')]);
    }
}
