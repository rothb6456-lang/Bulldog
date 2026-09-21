<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exercise extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'canonical_name',
        'movement_pattern',
        'equipment_id',
        'laterality',
        'exercise_category',
        'is_time_based',
        'is_distance_based',
        'preferred_replacements',
        'shoulder_safety_notes',
        'notes',
    ];

    protected $casts = [
        'is_time_based' => 'boolean',
        'is_distance_based' => 'boolean',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function bodyStructures(): BelongsToMany
    {
        return $this->belongsToMany(BodyStructure::class, 'exercise_body_structures')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function primaryBodyStructures(): BelongsToMany
    {
        return $this->bodyStructures()->wherePivot('role', 'primary');
    }

    public function secondaryBodyStructures(): BelongsToMany
    {
        return $this->bodyStructures()->wherePivot('role', 'secondary');
    }

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
