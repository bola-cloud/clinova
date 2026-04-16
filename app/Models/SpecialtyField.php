<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialtyField extends Model
{
    protected $fillable = ['specialty_id', 'label', 'type', 'options'];

    protected $casts = [
        'options' => 'array',
    ];

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }
}
