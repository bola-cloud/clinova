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
        'reminder_time',
        'is_completed',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'reminder_time' => 'string',
        'is_completed' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
