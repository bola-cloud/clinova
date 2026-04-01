<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'doctor_id',
        'consultation_fee',
        'followup_fee',
        'subscription_active',
        'subscription_expires_at',
        'secretary_name',
        'secretary_phone',
        'max_patients',
        'max_storage_gb',
        'used_storage_bytes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_active' => 'boolean',
            'subscription_expires_at' => 'datetime',
            'max_patients' => 'integer',
            'max_storage_gb' => 'float',
            'used_storage_bytes' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isSecretary(): bool
    {
        return $this->role === 'secretary';
    }

    public function assignedDoctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'doctor_id');
    }
}
