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
    public $queueNumber; // To display on success

    public $patientName;
    public $patientPhone;
    public $patientAgeYears;
    public $patientAddress;
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
        // No time slots to load in a pure queue system
    }

    public function confirmBooking()
    {
        $this->validate([
            'selectedDate' => 'required|date|after_or_equal:today',
            'patientName' => 'required|string|max:255',
            'patientPhone' => 'required|string|max:20',
            'patientAgeYears' => 'nullable|integer|min:0|max:150',
            'patientAddress' => 'nullable|string|max:500',
            'type' => 'required|in:checkup,follow_up',
        ]);

        $scheduledAt = Carbon::parse($this->selectedDate)->startOfDay();

        try {
            DB::transaction(function () use ($scheduledAt) {
                // Identify or create patient scoped strictly to this doctor
                $patient = Patient::where('doctor_id', $this->doctor->id)
                    ->where('phone', $this->patientPhone)
                    ->first();

                if (!$patient) {
                    $patient = Patient::create([
                        'doctor_id' => $this->doctor->id,
                        'name' => $this->patientName,
                        'phone' => $this->patientPhone,
                        'age_years' => $this->patientAgeYears,
                        'address' => $this->patientAddress,
                    ]);
                } else {
                    // Update age and address if provided and not already set
                    if ($this->patientAgeYears && !$patient->age_years) $patient->age_years = $this->patientAgeYears;
                    if ($this->patientAddress && !$patient->address) $patient->address = $this->patientAddress;
                    $patient->save();
                }

                // Prevent same patient from booking twice on the same day
                $hasBookingToday = Appointment::where('doctor_id', $this->doctor->id)
                    ->where('patient_id', $patient->id)
                    ->whereDate('scheduled_at', $this->selectedDate)
                    ->exists();

                if ($hasBookingToday) {
                    throw new \Exception(__('You already have an appointment booked on this date.'));
                }

                // Calculate next queue order for the date
                $maxQueue = Appointment::where('doctor_id', $this->doctor->id)
                    ->whereDate('scheduled_at', $this->selectedDate)
                    ->max('queue_order') ?? 0;
                
                $this->queueNumber = $maxQueue + 1;

                Appointment::create([
                    'doctor_id' => $this->doctor->id,
                    'patient_id' => $patient->id,
                    'scheduled_at' => $scheduledAt,
                    'type' => $this->type,
                    'status' => 'pending',
                    'queue_order' => $this->queueNumber,
                    'audit_log' => [['action' => 'Booked via Public Portal', 'timestamp' => now()->toIso8601String()]],
                ]);
            });

            $this->bookingSuccess = true;
            $this->errorMessage = null;

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.public.booking-portal')
            ->layout('layouts.portal', ['title' => __('Book Appointment') . ' - Dr. ' . $this->doctor->name]);
    }
}
