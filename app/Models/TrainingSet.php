<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingSet extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'session_id',
        'exercise_id',
        'set_number',
        'weight_lbs',
        'reps',
        'duration_seconds',
        'distance_meters',
        'rir',
        'tempo',
        'superset_group',
        'set_notes',
    ];

    protected $casts = [
        'set_number' => 'integer',
        'weight_lbs' => 'float',
        'reps' => 'float',
        'duration_seconds' => 'float',
        'distance_meters' => 'float',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class, 'session_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}