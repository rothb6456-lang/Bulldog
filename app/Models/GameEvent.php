<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameEvent extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_id',
        'sequence_number',
        'event_family',
        'event_type',
        'inning_number',
        'half_inning',
        'outs_before',
        'outs_after',
        'balls_before',
        'strikes_before',
        'balls_after',
        'strikes_after',
        'base_state_before',
        'base_state_after',
        'score_home_before',
        'score_home_after',
        'score_away_before',
        'score_away_after',
        'payload_json',
        'created_by_user_id',
        'is_voided',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'is_voided' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function eventPlayers(): HasMany
    {
        return $this->hasMany(GameEventPlayer::class);
    }
}