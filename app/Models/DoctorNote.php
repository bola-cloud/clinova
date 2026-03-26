<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorNote extends Model
{
    protected $fillable = [
        'doctor_id',
        'title',
        'content',
        'reminder_date',
        'is_completed',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
