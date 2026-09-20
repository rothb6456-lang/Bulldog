<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerTrainingGoal extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'player_identity_id',
        'phase_id',
        'goal_template_id',
        'title',
        'description',
        'target_metric_key',
        'target_value',
        'target_unit',
        'start_date',
        'target_date',
        'status',
        'achieved_at',
        'source',
        'created_by_user_id',
    ];

    protected $casts = [
        'target_value' => 'float',
        'start_date' => 'date',
        'target_date' => 'date',
        'achieved_at' => 'datetime',
    ];

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(TrainingPhase::class, 'phase_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TrainingGoalTemplate::class, 'goal_template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function isLongTerm(): bool
    {
        return $this->phase_id === null;
    }
}
