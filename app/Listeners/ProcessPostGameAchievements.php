<?php

namespace App\Listeners;

use App\Events\GameFinalized;
use App\Actions\Scoring\EvaluateGameStreaksAction;
use App\Actions\Scoring\EvaluatePlayerMilestonesAction;
use App\Actions\Scoring\AwardSystemAchievementsAction;
use Illuminate\Support\Facades\Log;

class ProcessPostGameAchievements
{
    protected EvaluateGameStreaksAction $evaluateStreaks;
    protected EvaluatePlayerMilestonesAction $evaluateMilestones;
    protected AwardSystemAchievementsAction $awardAchievements;

    public function __construct(
        EvaluateGameStreaksAction $evaluateStreaks,
        EvaluatePlayerMilestonesAction $evaluateMilestones,
        AwardSystemAchievementsAction $awardAchievements
    ) {
        $this->evaluateStreaks = $evaluateStreaks;
        $this->evaluateMilestones = $evaluateMilestones;
        $this->awardAchievements = $awardAchievements;
    }

    /**
     * Coordinate recalculation when a game is officially finalized.
     */
    public function handle(GameFinalized $event): void
    {
        $game = $event->game;

        try {
            // Sequence of evaluation tasks [100]
            $this->evaluateStreaks->execute($game);
            $this->evaluateMilestones->execute($game);
            $this->awardAchievements->execute($game);
        } catch (\Exception $e) {
            Log::error("Failed post-game processing for Game {$game->id}: " . $e->getMessage());
        }
    }
}