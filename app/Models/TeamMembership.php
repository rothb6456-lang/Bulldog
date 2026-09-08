<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMembership extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'team_id',
        'player_identity_id',
        'membership_type',
        'jersey_number',
        'positions_json',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'positions_json' => 'array', // Automatically serialize array to/from database JSON
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the team this membership belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the player identity associated with this membership.
     */
    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }
}