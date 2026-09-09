<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'canonical_name',
        'movement_pattern',
        'muscle_group',
        'category',
        'equipment_type',
        'is_unilateral',
        'is_timed',
        'safety_notes',
        'primary_muscle',
        'secondary_muscles',
        'default_equipment_id',
        'laterality',
        'exercise_category',
        'is_time_based',
        'is_distance_based',
        'preferred_replacements',
        'shoulder_safety_notes',
        'notes',
    ];

    protected $casts = [
        'is_unilateral' => 'boolean',
        'is_timed' => 'boolean',
        'is_time_based' => 'boolean',
        'is_distance_based' => 'boolean',
    ];

    public function nameMaps(): HasMany
    {
        return $this->hasMany(ExerciseNameMap::class, 'exercise_id');
    }

    public function trainingSets(): HasMany
    {
        return $this->hasMany(TrainingSet::class, 'exercise_id');
    }

    public function personalRecords(): HasMany
    {
        return $this->hasMany(PlayerPr::class, 'exercise_id');
    }
}
