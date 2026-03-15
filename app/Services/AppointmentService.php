<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AppointmentService extends BaseService
{
    public function getAppointmentsForDoctor(int $doctorId, Carbon $date): Collection
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('scheduled_at', $date)
            ->with('patient')
            ->orderBy('scheduled_at')
            ->get();
    }

    public function bookAppointment(array $data): Appointment
    {
        $date = Carbon::parse($data['scheduled_at'])->toDateString();
        
        // Check for existing appointment for the same patient on the same day
        $existingCount = Appointment::where('patient_id', $data['patient_id'])
            ->whereDate('scheduled_at', $date)
            ->count();
            
        if ($existingCount > 0) {
             throw \Illuminate\Validation\ValidationException::withMessages([
                'bookingDate' => __('This patient already has an appointment on the selected date.'),
            ]);
        }

        // Auto-assign to the end of the queue for the specific doctor and date
        if (!isset($data['queue_order'])) {
            $date = Carbon::parse($data['scheduled_at'])->toDateString();
            $maxQueue = Appointment::where('doctor_id', $data['doctor_id'])
                ->whereDate('scheduled_at', $date)
                ->max('queue_order');
            
            $data['queue_order'] = $maxQueue !== null ? $maxQueue + 1 : 1;
        }

        $appointment = Appointment::create($data);
        $this->logAction($appointment, 'created');
        return $appointment;
    }

    public function reorderQueue(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            $appointment = Appointment::find($id);
            if ($appointment && $appointment->queue_order !== $index + 1) {
                $oldOrder = $appointment->queue_order;
                $appointment->update(['queue_order' => $index + 1]);
                $this->logAction($appointment, "changed queue order from {$oldOrder} to " . ($index + 1));
            }
        }
    }

    public function updateStatus(Appointment $appointment, string $status): void
    {
        $oldStatus = $appointment->status;
        $appointment->update(['status' => $status]);
        $this->logAction($appointment, "changed status from {$oldStatus} to {$status}");
    }

    protected function logAction(Appointment $appointment, string $action): void
    {
        $logs = $appointment->audit_log ?? [];
        $logs[] = [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'System',
            'action' => $action,
            'timestamp' => now()->toDateTimeString(),
        ];
        $appointment->update(['audit_log' => $logs]);
    }
}
