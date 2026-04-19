<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory;

    protected static function booted()
    {
        static::deleting(function ($patient) {
            // Delete standalone medical files
            foreach ($patient->files as $file) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file->file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);
                }
            }

            // Delete visit attachments (prescriptions, etc.)
            foreach ($patient->visits as $visit) {
                if ($visit->treatment_file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($visit->treatment_file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($visit->treatment_file_path);
                }
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
