<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'player_identity_id',
        'phase_id',
        'session_date',
        'phase_week',
        'program_day',
        'workout_name',
        'session_start_time',
        'session_end_time',
        'duration_minutes',
        'gym_location',
        'bodyweight_lbs',
        'rpe_overall',
        'exercise_count',
        'general_notes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'session_start_time' => 'datetime',
        'session_end_time' => 'datetime',
        'phase_week' => 'integer',
        'program_day' => 'float',
        'duration_minutes' => 'integer',
        'bodyweight_lbs' => 'float',
        'rpe_overall' => 'float',
        'exercise_count' => 'integer',
    ];

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(TrainingPhase::class, 'phase_id');
    }

    public function sets(): HasMany
    {
        return $this->hasMany(TrainingSet::class, 'session_id');
    }

    public function personalRecords(): HasMany
    {
        return $this->hasMany(PlayerPr::class, 'session_id');
    }
}