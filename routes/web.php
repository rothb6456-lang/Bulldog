<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\Web\GameScoringController;
use App\Http\Controllers\Web\ImportWizardController;
use App\Http\Controllers\Web\PlayerCareerController;
use App\Http\Controllers\Web\ShareLinkController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\EcosystemController;
use App\Http\Controllers\Web\TeamWorkspaceController;
use App\Http\Controllers\Web\GuardianController;
use App\Http\Controllers\Api\V1\Auth\MomentumLaunchController;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/teams', [TeamWorkspaceController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamWorkspaceController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamWorkspaceController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}', [TeamWorkspaceController::class, 'show'])->name('teams.show');
    Route::post('/teams/{team}/roster', [TeamWorkspaceController::class, 'roster'])->name('teams.roster');
    Route::get('/guardians', [GuardianController::class, 'index'])->name('guardians.index');
    Route::post('/teams/{team}/guardians', [GuardianController::class, 'invite'])->name('guardians.invite');
    Route::post('/guardians/{relationship}/accept', [GuardianController::class, 'accept'])->name('guardians.accept');
    Route::post('/guardians/{relationship}/confirm', [GuardianController::class, 'confirm'])->name('guardians.confirm');
    Route::post('/guardians/{relationship}/revoke', [GuardianController::class, 'revoke'])->name('guardians.revoke');
    Route::get('/players/{player}', [EcosystemController::class, 'player'])->name('players.show');
    Route::get('/players', function (\Illuminate\Http\Request $request, \App\Actions\Training\ResolveTrainingIdentity $resolve) {
        return redirect()->route('players.show', $resolve->execute($request->user()));
    })->name('players.mine');
    Route::post('/momentum/launch', [MomentumLaunchController::class, 'launch'])->middleware('throttle:15,1')->name('momentum.launch');
    Route::view('/nutrition', 'ecosystem.nutrition')->name('nutrition');
    Route::view('/merch', 'ecosystem.merch')->name('merch');
    Route::post('/games/practice', [GameController::class, 'demo'])->name('games.demo');
    Route::post('/games/{game}/start', [GameController::class, 'start'])->name('games.start');
    Route::post('/games/{game}/finalize', [GameController::class, 'finalize'])->name('games.finalize');
    Route::post('/games/{game}/score', [GameScoringController::class, 'store'])->name('games.score.store');
    Route::put('/games/{game}/lineup', [\App\Http\Controllers\Web\GameSetupController::class, 'lineup']);
    Route::post('/games/{game}/lineup/add', [\App\Http\Controllers\Web\GameSetupController::class, 'add']);
    Route::put('/games/{game}/defense', [\App\Http\Controllers\Web\GameSetupController::class, 'defense']);
    Route::put('/games/{game}/roster', [\App\Http\Controllers\Web\GameSetupController::class, 'roster']);
    Route::post('/games/{game}/roster/load-from-team', [\App\Http\Controllers\Web\GameSetupController::class, 'reloadRoster']);

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

Route::get('/dashboard', [EcosystemController::class, 'hub'])->middleware('auth')->name('dashboard');

// High-entropy token resolution route (outside auth, controlled display)
Route::get('/shared/cards/{token}', [ShareLinkController::class, 'show'])
    ->name('share.resolve');

require __DIR__ . '/auth.php';
