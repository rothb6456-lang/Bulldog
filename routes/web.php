<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\Web\GameScoringController;
use App\Http\Controllers\Web\ImportWizardController;
use App\Http\Controllers\Web\PlayerCareerController;
use App\Http\Controllers\Web\ShareLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Game Scheduling and Workspace Routes
    Route::get('/games/create', [GameController::class, 'create'])->name('games.create');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');

    // Live Scoring Console View
    Route::get('/games/{game}/score', [GameScoringController::class, 'show'])->name('games.score');

    // Player Career Page
    Route::get('/players/{player}/career', [PlayerCareerController::class, 'show'])->name('players.career');

    // Generate secure share token for a player identity
    Route::post('/sharing/players/{player}/card', [ShareLinkController::class, 'store'])
        ->name('share.player.card');

    // Historical Import UI Wizard
    Route::get('/imports/create', [ImportWizardController::class, 'create'])->name('imports.create');
    Route::post('/imports', [ImportWizardController::class, 'store'])->name('imports.store');
    Route::get('/imports/{import}/mapping', [ImportWizardController::class, 'mapping'])->name('imports.mapping');
    Route::post('/imports/{import}/confirm', [ImportWizardController::class, 'confirm'])->name('imports.confirm');
});

// High-entropy token resolution route (outside auth, controlled display)
Route::get('/shared/cards/{token}', [ShareLinkController::class, 'show'])
    ->name('share.resolve');

require __DIR__ . '/auth.php';
