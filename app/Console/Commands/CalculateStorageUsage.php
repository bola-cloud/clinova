<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PatientFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CalculateStorageUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-storage-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculates and updates used_storage_bytes for all doctors.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\User> $doctors */
        $doctors = User::where('role', 'doctor')->get();

        $this->info("Calculating storage usage for " . $doctors->count() . " doctors...");

        foreach ($doctors as $doctor) {
            /** @var \App\Models\User $doctor */
            $totalBytes = 0;
            
            // Files uploaded by this doctor (including those for their patients)
            // Or files belonging to patients of this doctor.
            // Usually, a doctor owns the storage of their patients.
            
            $patientIds = $doctor->patients()->pluck('id');
            $files = PatientFile::whereIn('patient_id', $patientIds)->get();

            foreach ($files as $file) {
                if (Storage::disk('public')->exists($file->file_path)) {
                    $totalBytes += Storage::disk('public')->size($file->file_path);
                }
            }

            $doctor->update(['used_storage_bytes' => $totalBytes]);
            $this->line("Doctor {$doctor->name}: " . number_format($totalBytes / 1024 / 1024, 2) . " MB");
        }

        $this->info("Calculation complete.");
    }
}
