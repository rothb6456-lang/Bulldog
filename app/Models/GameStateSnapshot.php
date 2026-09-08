<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameStateSnapshot extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_id',
        'game_event_id',
        'sequence_number',
        'inning_number',
        'half_inning',
        'outs',
        'balls',
        'strikes',
        'score_home',
        'score_away',
        'base_state',
        'current_batter_id',
        'current_pitcher_id',
        'lineup_pointers',
        'defensive_alignment',
    ];

    protected $casts = [
        'lineup_pointers' => 'array',
        'defensive_alignment' => 'array',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function gameEvent(): BelongsTo
    {
        return $this->belongsTo(GameEvent::class);
    }
}