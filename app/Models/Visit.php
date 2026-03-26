<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    /** @use HasFactory<\Database\Factories\VisitFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'complaint',
        'diagnosis',
        'history',
        'treatment_text',
        'treatment_file_path',
        'family_history',
        'parent_visit_id',
        'type',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(PatientFile::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(Visit::class, 'parent_visit_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'parent_visit_id');
    }
}
