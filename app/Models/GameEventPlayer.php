<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameEventPlayer extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_event_id',
        'player_identity_id',
        'role',
    ];

    public function gameEvent(): BelongsTo
    {
        return $this->belongsTo(GameEvent::class);
    }

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class);
    }
}