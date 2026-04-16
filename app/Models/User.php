<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'max_patients',
        'max_storage_gb',
        'used_storage_bytes',
        'specialty_id',
        'subscription_plan',
        'subscription_price',
        'is_paid',
        'subscription_start_at',
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
            'is_paid' => 'boolean',
            'subscription_start_at' => 'datetime',
            'subscription_price' => 'decimal:2',
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

    public function secretaries()
    {
        return $this->hasMany(User::class, 'doctor_id')->where('role', 'secretary');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'doctor_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    /**
     * Calculate total allowed storage in bytes based on plan and duration.
     */
    public function getTotalAllowedStorageBytes(): int
    {
        if (!$this->max_storage_gb) {
            return 0; // Unlimited or not set
        }

        $baseBytes = (int)($this->max_storage_gb * 1024 * 1024 * 1024);

        // If monthly, multiply by months since start
        if (str_contains(strtolower($this->subscription_plan), 'monthly')) {
            $months = $this->getMonthsSubscribedCount();
            return $baseBytes * $months;
        }

        // Yearly or others: Fixed limit
        return $baseBytes;
    }

    /**
     * Check if the doctor has enough space for a new file.
     */
    public function hasStorageSpace(int $additionalBytes = 0): bool
    {
        $allowed = $this->getTotalAllowedStorageBytes();
        
        if ($allowed === 0) {
            return true; // Unlimited
        }

        return ($this->used_storage_bytes + $additionalBytes) <= $allowed;
    }

    /**
     * Count months since subscription started.
     */
    private function getMonthsSubscribedCount(): int
    {
        if (!$this->subscription_start_at) {
            return 1;
        }

        $start = $this->subscription_start_at->startOfDay();
        $now = now()->startOfDay();

        $diffInMonths = $start->diffInMonths($now);

        // Even if it's the first day, it counts as 1 month quota
        return (int)$diffInMonths + 1;
    }

    /**
     * Get human readable usage.
     */
    public function getStorageUsageText(): string
    {
        $used = $this->used_storage_bytes / 1024 / 1024 / 1024; // GB
        $allowed = $this->getTotalAllowedStorageBytes() / 1024 / 1024 / 1024; // GB
        
        if ($allowed == 0) return number_format($used, 2) . " GB / Unlimited";
        
        return number_format($used, 2) . " GB / " . number_format($allowed, 2) . " GB";
    }
}
