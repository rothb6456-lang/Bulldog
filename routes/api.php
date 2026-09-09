<?php

use App\Http\Controllers\Api\Imports\HistoricalImportApiController;
use App\Http\Controllers\Api\Scoring\GameEventController;
use App\Http\Controllers\Api\Teams\RosterController;
use App\Http\Controllers\Api\Teams\TeamController;
use App\Http\Controllers\Api\V1\Training\CoachController;
use App\Http\Controllers\Api\V1\Training\TrainingSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Team management endpoints
    Route::post('/teams', [TeamController::class, 'store']);
    Route::get('/teams/{team}', [TeamController::class, 'show']);

    // Roster management within a team context
    Route::post('/teams/{team}/roster', [RosterController::class, 'store']);
    // Game Scoring Event Stream
    Route::post('/games/{game}/events', [GameEventController::class, 'store']);
    Route::get('/games/{game}/events', [GameEventController::class, 'index']);
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Momentum Training Engine Routes
    Route::get('/training/sessions', [TrainingSessionController::class, 'index']);
    Route::post('/training/sessions', [TrainingSessionController::class, 'store']);
    Route::get('/training/sessions/{session}', [TrainingSessionController::class, 'show']);
    Route::post('/training/coach/generate-card', [CoachController::class, 'generateCard']);

    Route::post('/imports', [HistoricalImportApiController::class, 'store']);
    Route::post('/imports/{import}/confirm', [HistoricalImportApiController::class, 'confirm']);
    Route::get('/imports/{import}', [HistoricalImportApiController::class, 'show']);
});
