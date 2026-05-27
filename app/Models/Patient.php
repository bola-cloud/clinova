<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::deleting(function ($patient) {
            /** @var Patient $patient */
            if (!$patient->isForceDeleting()) {
                return; // Do not delete physical files on soft delete
            }

            $doctor = $patient->doctor;
            $totalDeletedSize = 0;

            // 1. Calculate and delete standalone medical files
            foreach ($patient->files()->withTrashed()->get() as $file) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file->file_path)) {
                    $totalDeletedSize += \Illuminate\Support\Facades\Storage::disk('public')->size($file->file_path);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
                }
            }

            // 2. Calculate and delete visit attachments (legacy treatment_file_path)
            foreach ($patient->visits()->withTrashed()->get() as $visit) {
                if ($visit->treatment_file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($visit->treatment_file_path)) {
                    $totalDeletedSize += \Illuminate\Support\Facades\Storage::disk('public')->size($visit->treatment_file_path);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($visit->treatment_file_path);
                }
            }

            // 3. Decrement doctor storage
            if ($doctor && $totalDeletedSize > 0) {
                $doctor->decrement('used_storage_bytes', min($totalDeletedSize, $doctor->used_storage_bytes));
            }
        });
    }

    protected $fillable = [
        'name',
        'phone',
        'age_years',
        'age_months',
        'age_days',
        'weight',
        'address',
        'family_history',
        'personal_history',
        'tags',
        'doctor_id',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(PatientFile::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * Get the total count of files (standalone + visit attachments).
     */
    public function getTotalFilesCountAttribute(): int
    {
        $visitAttachmentsCount = $this->visits()->whereNotNull('treatment_file_path')->count();
        return $this->files()->count() + $visitAttachmentsCount;
    }

    /**
     * Check if the patient has any medical files.
     */
    public function getHasMedicalFilesAttribute(): bool
    {
        return $this->total_files_count > 0;
    }
}
