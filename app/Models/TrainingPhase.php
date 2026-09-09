<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingPhase extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'player_identity_id',
        'phase_number',
        'name',
        'start_date',
        'end_date',
        'duration_weeks',
        'sessions_per_week',
        'phase_goal',
        'key_exercises',
        'primary_metrics',
        'secondary_metrics',
        'joint_notes',
        'grip_status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'phase_number' => 'integer',
        'duration_weeks' => 'integer',
        'sessions_per_week' => 'integer',
    ];

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class, 'phase_id');
    }

    public function assumptions(): HasMany
    {
        return $this->hasMany(PlayerTrainingAssumption::class, 'phase_id_first_observed');
    }

    public function personalRecords(): HasMany
    {
        return $this->hasMany(PlayerPr::class, 'phase_id');
    }
}
