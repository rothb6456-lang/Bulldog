<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruleset extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'sport_id',
        'name',
        'config_json',
    ];

    protected $casts = [
        'config_json' => 'array',
    ];

    /**
     * Get the sport associated with this ruleset.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Get games utilizing this ruleset configuration.
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
