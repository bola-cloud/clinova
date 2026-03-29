<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'age',
        'weight',
        'address',
        'family_history',
        'personal_history',
        'chronic_illnesses',
        'tags',
        'doctor_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'chronic_illnesses' => 'array',
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
}
