<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'external_code',
        'name',
        'category',
        'manufacturer',
        'gym_location',
        'notes',
    ];

    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class, 'equipment_id');
    }
}
