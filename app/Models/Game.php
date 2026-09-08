<?php

namespace App\Models;

use App\Events\GameFinalized;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory, HasUuid;

    protected static function booted(): void
    {
        static::saved(function (self $game): void {
            if ($game->status === 'finalized' && $game->wasChanged('status')) {
                event(new GameFinalized($game));
            }
        });
    }

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_code',
        'sport_id',
        'home_team_id',
        'away_team_id',
        'scheduled_at',
        'location',
        'ruleset_id',
        'status',
        'ownership_team_id',
        'created_by_user_id',
        'finalized_at',
        'finalized_by_user_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    /**
     * Get the sport played.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Get the home team.
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the away team.
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Get the ruleset applied.
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Get the roster entries loaded for this game.
     */
    public function rosterEntries(): HasMany
    {
        return $this->hasMany(GameRosterEntry::class);
    }

    /**
     * Get the lineup entries configured.
     */
    public function lineupEntries(): HasMany
    {
        return $this->hasMany(LineupEntry::class);
    }

    /**
     * Get the defensive assignments on the field.
     */
    public function defensiveAssignments(): HasMany
    {
        return $this->hasMany(DefensiveAssignment::class);
    }

    /**
     * Get the user who created this game record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
