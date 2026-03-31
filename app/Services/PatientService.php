<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Collection;

class PatientService extends BaseService
{
    public function getAllPatients(): Collection
    {
        return Patient::all();
    }

    public function createPatient(array $data): Patient
    {
        $doctorId = $data['doctor_id'] ?? null;
        if ($doctorId) {
            $doctor = \App\Models\User::find($doctorId);
            if ($doctor && $doctor->max_patients > 0 && $doctor->patients()->count() >= $doctor->max_patients) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'name' => [__('The patient limit for this clinic has been reached. Please contact administration.')]
                ]);
            }
        }

        return Patient::create($data);
    }

    public function getPatientWithHistory(int $id): Patient
    {
        return Patient::with(['visits', 'appointments', 'files'])->findOrFail($id);
    }

    public function updatePatient(int $id, array $data): Patient
    {
        $patient = Patient::findOrFail($id);
        $patient->update($data);
        return $patient;
    }
}
