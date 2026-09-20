<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerTrainingGuardrail extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'player_identity_id',
        'body_region',
        'restricted_movement_patterns',
        'restriction_type',
        'description',
        'status',
        'source',
        'created_by_user_id',
        'first_noted_date',
        'resolved_date',
    ];

    protected $casts = [
        'restricted_movement_patterns' => 'array',
        'first_noted_date' => 'date',
        'resolved_date' => 'date',
    ];

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
