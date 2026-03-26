<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can view the patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        // Admins can view all patients
        if ($user->isAdmin()) {
            return true;
        }

        // Doctors can view their own patients
        if ($user->isDoctor()) {
            return $user->id === $patient->doctor_id;
        }

        // Secretaries can view patients belonging to their assigned doctor
        if ($user->isSecretary()) {
            return $user->doctor_id === $patient->doctor_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create patients.
     */
    public function create(User $user): bool
    {
        return $user->isDoctor() || $user->isSecretary();
    }

    /**
     * Determine whether the user can update the patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $this->view($user, $patient);
    }

    /**
     * Determine whether the user can delete the patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        // Only admins or the owning doctor can delete for now
        if ($user->isAdmin()) return true;
        if ($user->isDoctor()) return $user->id === $patient->doctor_id;
        
        return false;
    }
}
