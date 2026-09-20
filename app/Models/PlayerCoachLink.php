<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerCoachLink extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'coach_user_id',
        'player_identity_id',
        'status',
        'invited_by',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_user_id');
    }

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
