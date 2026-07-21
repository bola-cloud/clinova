<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'specialties_data' => 'array',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
