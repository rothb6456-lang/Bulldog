<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerPr extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'player_identity_id',
        'exercise_id',
        'pr_code',
        'pr_type',
        'pr_value',
        'pr_unit',
        'pr_date',
        'phase_id',
        'session_id',
        'previous_best',
        'previous_best_date',
        'set_details',
        'notes',
    ];

    protected $casts = [
        'pr_value' => 'float',
        'previous_best' => 'float',
        'pr_date' => 'date',
        'previous_best_date' => 'date',
    ];

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(TrainingPhase::class, 'phase_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'session_id');
    }
}