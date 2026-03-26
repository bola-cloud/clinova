<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view the appointment.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        // Admins can view all
        if ($user->isAdmin()) return true;

        // Doctors view their own
        if ($user->isDoctor()) {
            return $user->id === $appointment->doctor_id;
        }

        // Secretaries view their assigned doctor's
        if ($user->isSecretary()) {
            return $user->doctor_id === $appointment->doctor_id;
        }

        return false;
    }

    /**
     * Determine whether the user can record a visit for the appointment.
     */
    public function recordVisit(User $user, Appointment $appointment): bool
    {
        // Only the assigned doctor can record a visit (secretaries cannot start visits)
        return $user->isDoctor() && $user->id === $appointment->doctor_id;
    }

    /**
     * Determine whether the user can update the appointment.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $this->view($user, $appointment);
    }

    /**
     * Determine whether the user can delete the appointment.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isDoctor()) return $user->id === $appointment->doctor_id;
        return false;
    }
}
