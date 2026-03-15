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
