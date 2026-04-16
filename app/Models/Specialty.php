<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    protected $fillable = ['name'];

    public function fields(): HasMany
    {
        return $this->hasMany(SpecialtyField::class);
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(User::class, 'specialty_id');
    }
}
