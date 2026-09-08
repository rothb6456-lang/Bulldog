<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayerStat extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'game_player_stats';

    protected $fillable = [
        'game_id',
        'player_identity_id',
        'team_id',
        'stat_key',
        'stat_value',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}