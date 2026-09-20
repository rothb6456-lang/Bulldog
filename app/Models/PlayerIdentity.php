<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlayerIdentity extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'player_code',
        'user_id',
        'claim_status',
        'display_name',
        'birth_year',
        'created_by_user_id',
    ];

    /**
     * Get the claimed user account linked to this identity, if any.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user creator (typically a coach) who initialized this player identity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Momentum: the athlete's lean settings profile (1:1).
     */
    public function trainingProfile(): HasOne
    {
        return $this->hasOne(PlayerTrainingProfile::class, 'player_identity_id');
    }

    /**
     * Momentum: long-term and phase-scoped training goals.
     */
    public function trainingGoals(): HasMany
    {
        return $this->hasMany(PlayerTrainingGoal::class, 'player_identity_id');
    }

    /**
     * Momentum: declared equipment/facility access.
     */
    public function equipmentAccess(): HasMany
    {
        return $this->hasMany(PlayerEquipmentAccess::class, 'player_identity_id');
    }

    /**
     * Momentum: durable training guardrails/limitations (active and historical).
     */
    public function trainingGuardrails(): HasMany
    {
        return $this->hasMany(PlayerTrainingGuardrail::class, 'player_identity_id');
    }

    /**
     * Momentum: coach/PT links where this identity is the athlete being coached.
     */
    public function coachLinks(): HasMany
    {
        return $this->hasMany(PlayerCoachLink::class, 'player_identity_id');
    }
}