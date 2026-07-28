<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\User;
use App\Models\DoctorSchedule;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingPortal extends Component
{
    public $slug;
    public $doctor;

    public $selectedDate;
    public $availableSlots = [];
    public $selectedTime;

    public $patientName;
    public $patientPhone;
    public $type = 'checkup';

    public $bookingSuccess = false;
    public $errorMessage = null;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->doctor = User::where('booking_slug', $slug)->firstOrFail();

        if (!$this->doctor->is_booking_active || !$this->doctor->isDoctor()) {
            abort(404, 'Booking is not available for this doctor.');
        }

        $this->selectedDate = now()->format('Y-m-d');
        $this->loadSlots();
    }

    public function updatedSelectedDate()
    {
        $this->selectedTime = null;
        $this->loadSlots();
    }

    public function loadSlots()
    {
        $this->availableSlots = [];
        if (!$this->selectedDate) return;

        $date = Carbon::parse($this->selectedDate);
        if ($date->isBefore(now()->startOfDay())) {
            return; // Cannot book in the past
        }

        $dayOfWeek = $date->dayOfWeek;
        $schedule = DoctorSchedule::where('doctor_id', $this->doctor->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_working_day', true)
            ->first();

        if (!$schedule) return;

        $slotDuration = $schedule->slot_duration_minutes;
        $startTime = Carbon::parse($this->selectedDate . ' ' . $schedule->start_time);
        $endTime = Carbon::parse($this->selectedDate . ' ' . $schedule->end_time);

        // Fetch existing appointments for this doctor on this day
        $existingAppointments = Appointment::where('doctor_id', $this->doctor->id)
            ->whereDate('scheduled_at', $this->selectedDate)
            ->where('status', '!=', 'cancelled')
            ->pluck('scheduled_at')
            ->map(fn($dt) => Carbon::parse($dt)->format('H:i'))
            ->toArray();

        $currentTime = $startTime->copy();
        
        while ($currentTime->copy()->addMinutes($slotDuration)->lte($endTime)) {
            $timeString = $currentTime->format('H:i');
            
            // Only add if not in past (if today)
            if ($date->isToday() && $currentTime->isBefore(now())) {
                $currentTime->addMinutes($slotDuration);
                continue;
            }

            if (!in_array($timeString, $existingAppointments)) {
                $this->availableSlots[] = $timeString;
            }

            $currentTime->addMinutes($slotDuration);
        }
    }

    public function selectTime($time)
    {
        $this->selectedTime = $time;
    }

    public function confirmBooking()
    {
        $this->validate([
            'selectedDate' => 'required|date|after_or_equal:today',
            'selectedTime' => 'required',
            'patientName' => 'required|string|max:255',
            'patientPhone' => 'required|string|max:20',
            'type' => 'required|in:checkup,follow_up',
        ]);

        $scheduledAt = Carbon::parse($this->selectedDate . ' ' . $this->selectedTime);

        try {
            DB::transaction(function () use ($scheduledAt) {
                // Lock check to prevent double booking race condition
                $exists = Appointment::where('doctor_id', $this->doctor->id)
                    ->where('scheduled_at', $scheduledAt)
                    ->where('status', '!=', 'cancelled')
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    throw new \Exception(__('This slot has just been booked by someone else. Please choose another time.'));
                }

                // Identify or create patient scoped strictly to this doctor
                $patient = Patient::where('doctor_id', $this->doctor->id)
                    ->where('phone', $this->patientPhone)
                    ->first();

                if (!$patient) {
                    $patient = Patient::create([
                        'doctor_id' => $this->doctor->id,
                        'name' => $this->patientName,
                        'phone' => $this->patientPhone,
                        'age' => 0, // Default or nullable depending on schema
                        'gender' => 'male', // Default
                    ]);
                }

                // Prevent same patient from booking twice on the same day
                $hasBookingToday = Appointment::where('doctor_id', $this->doctor->id)
                    ->where('patient_id', $patient->id)
                    ->whereDate('scheduled_at', $this->selectedDate)
                    ->exists();

                if ($hasBookingToday) {
                    throw new \Exception(__('You already have an appointment booked on this date.'));
                }

                Appointment::create([
                    'doctor_id' => $this->doctor->id,
                    'patient_id' => $patient->id,
                    'scheduled_at' => $scheduledAt,
                    'type' => $this->type,
                    'status' => 'pending',
                    'notes' => 'Booked via Public Portal',
                ]);
            });

            $this->bookingSuccess = true;
            $this->errorMessage = null;

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->loadSlots(); // Refresh slots
            $this->selectedTime = null;
        }
    }

    public function render()
    {
        return view('livewire.public.booking-portal')
            ->layout('layouts.portal', ['title' => __('Book Appointment') . ' - Dr. ' . $this->doctor->name]);
    }
}
