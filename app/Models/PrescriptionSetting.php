<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'background_image_path',
        'elements',
    ];

    protected $casts = [
        'elements' => 'array',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
