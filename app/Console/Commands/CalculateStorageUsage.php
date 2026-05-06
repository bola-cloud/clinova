<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\PatientFile;
use App\Models\Visit;
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
            
            // 1. Count Patient Files (Uploaded directly or via visits)
            $patientIds = $doctor->patients()->pluck('id');
            $files = PatientFile::whereIn('patient_id', $patientIds)->get();

            foreach ($files as $file) {
                if (Storage::disk('public')->exists($file->file_path)) {
                    $totalBytes += Storage::disk('public')->size($file->file_path);
                }
            }

            // 2. Count Legacy Visit Attachments (treatment_file_path)
            $visitAttachments = Visit::whereIn('patient_id', $patientIds)
                ->whereNotNull('treatment_file_path')
                ->get();

            foreach ($visitAttachments as $visit) {
                if (Storage::disk('public')->exists($visit->treatment_file_path)) {
                    $totalBytes += Storage::disk('public')->size($visit->treatment_file_path);
                }
            }

            // 3. Count Doctor's Profile Image
            if ($doctor->profile_image && Storage::disk('public')->exists($doctor->profile_image)) {
                $totalBytes += Storage::disk('public')->size($doctor->profile_image);
            }

            $doctor->update(['used_storage_bytes' => $totalBytes]);
            $this->line("Doctor {$doctor->name}: " . number_format($totalBytes / 1024 / 1024, 2) . " MB");
        }

        $this->info("Calculation complete.");
    }
}
