<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BodyStructure extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'type',
        'region',
        'short_description',
        'function_notes',
        'common_issues',
        'reference_notes',
        'sort_order',
    ];

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'exercise_body_structures')
            ->withPivot('role')
            ->withTimestamps();
    }
}
