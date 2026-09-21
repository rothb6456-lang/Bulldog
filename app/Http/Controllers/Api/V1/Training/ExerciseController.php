<?php

namespace App\Http\Controllers\Api\V1\Training;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    /**
     * List the canonical exercise catalog, alphabetical by name.
     *
     * GET /api/v1/training/exercises
     */
    public function index(): JsonResponse
    {
        $exercises = Exercise::query()
            ->with(['equipment:id,name,category', 'bodyStructures:id,name,type,region'])
            ->orderBy('canonical_name')
            ->get([
                'id',
                'canonical_name',
                'exercise_category',
                'movement_pattern',
                'equipment_id',
                'laterality',
                'shoulder_safety_notes',
            ]);

        return response()->json([
            'status' => 'success',
            'data' => $exercises,
        ]);
    }
}