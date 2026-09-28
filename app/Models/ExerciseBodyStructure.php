<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExerciseBodyStructure extends Pivot
{
    use HasUuids;

    protected $table = 'exercise_body_structures';

    public $incrementing = false;

    protected $keyType = 'string';
}
