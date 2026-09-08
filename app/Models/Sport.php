<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * User profiles associated with this sport as their primary interest.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(UserProfile::class, 'primary_sport_id');
    }
}