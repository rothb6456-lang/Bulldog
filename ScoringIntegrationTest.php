<?php

namespace Tests\Feature\Scoring;

use App\Actions\Scoring\RecordScoringEventAction;
use App\Models\Game;
use App\Models\GameStateSnapshot;
use App\Models\PlayerIdentity;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Models\GamePlayerStat;
use App\Models\GameTeamStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ScoringIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function recording_a_sequence_of_scoring_events_correctly_projects_player_and_team_stats()
    {
        // ==========================================
        // 1. ARRANGE: Set up the baseline database
        // ==========================================

        // Create the core Sport (Baseball)
        $sport = Sport::create([
            'id' => '90000000-0000-0000-0000-000000000001',
            'code' => 'baseball',
            'name' => 'Baseball',
        ]);

        // Create the authorized user (Scorekeeper/Coach)
        $user = User::create([
            'id' => '80000000-0000-0000-0000-000000000001',
            'email' => 'scorekeeper@bulldogstats.com',
            'password' => bcrypt('password123'),
            'name' => 'Coach Hayes',
            'is_minor' => false,
            'status' => 'active',
        ]);

        $user->profile()->create([
            'display_name' => 'Coach Hayes',
            'primary_sport_id' => $sport->id,
        ]);

        // Create Home & Away Teams
        $homeTeam = Team::create([
            'id' => '70000000-0000-0000-0000-000000000001',
            'team_code' => 'TM-BULLDOGS',
            'name' => 'Springfield Bulldogs 12U',
            'sport_id' => $sport->id,
            'created_by_user_id' => $user->id,
            'status' => 'active',
        ]);

        $awayTeam = Team::create([
            'id' => '70000000-0000-0000-0000-000000000002',
            'team_code' => 'TM-CARDINALS',
            'name' => 'Springfield Cardinals 12U',
            'sport_id' => $sport->id,
            'created_by_user_id' => $user->id,
            'status' => 'active',
        ]);

        // Give the user scoring/admin authority on the home team
        $homeTeam->roleAssignments()->create([
            'user_id' => $user->id,
            'role_type' => 'team_admin',
            'granted_by_user_id' => $user->id,
        ]);

        // Create Player Identities
        // Batter belongs to Home Team (Bulldogs)
        $batter = PlayerIdentity::create([
            'id' => '60000000-0000-0000-0000-000000000001',
            'player_code' => 'PLY-JONNYD',
            'display_name' => 'Jonny Damon',
            'claim_status' => 'unclaimed',
            'created_by_user_id' => $user->id,
        ]);

        $homeTeam->memberships()->create([
            'player_identity_id' => $batter->id,
            'membership_type' => 'player',
            'jersey_number' => '18',
            'status' => 'active',
        ]);

        // Pitcher belongs to Away Team (Cardinals)
        $pitcher = PlayerIdentity::create([
            'id' => '60000000-0000-0000-0000-000000000002',
            'player_code' => 'PLY-PITCHER',
            'display_name' => 'Roger Clemens',
            'claim_status' => 'unclaimed',
            'created_by_user_id' => $user->id,
        ]);

        $awayTeam->memberships()->create([
            'player_identity_id' => $pitcher->id,
            'membership_type' => 'player',
            'jersey_number' => '21',
            'status' => 'active',
        ]);

        // Schedule the game (default ownership matches home team)
        $game = Game::create([
            'id' => '50000000-0000-0000-0000-000000000001',
            'game_code' => 'GM-FALL2026-01',
            'sport_id' => $sport->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'ownership_team_id' => $homeTeam->id,
            'scheduled_at' => now(),
            'location' => 'Springfield Field A',
            'status' => 'in_progress',
            'created_by_user_id' => $user->id,
        ]);

        // Establish the initial game state snapshot (Sequence 0, Top of the 1st, vacant bases)
        GameStateSnapshot::create([
            'game_id' => $game->id,
            'game_event_id' => null,
            'sequence_number' => 0,
            'inning_number' => 1,
            'half_inning' => 'top',
            'outs' => 0,
            'balls' => 0,
            'strikes' => 0,
            'score_home' => 0,
            'score_away' => 0,
            'base_state' => '000',
            'current_batter_id' => $batter->id,
            'current_pitcher_id' => $pitcher->id,
        ]);

        // Authenticate the scoring operator
        Sanctum::actingAs($user);

        // Resolve Action handler
        $recorderAction = app(RecordScoringEventAction::class);

        // ==========================================
        // 2. ACT: Record events
        // ==========================================

        // Pitch 1: Ball
        $recorderAction->execute($game->id, [
            'event_family' => 'pitching',
            'event_type' => 'pitch',
            'current_batter_id' => $batter->id,
            'current_pitcher_id' => $pitcher->id,
            'payload' => ['pitch_result' => 'ball'],
            'players' => [
                ['player_identity_id' => $pitcher->id, 'role' => 'pitcher'],
                ['player_identity_id' => $batter->id, 'role' => 'batter'],
            ]
        ], $user->id);

        // Pitch 2: Strike
        $recorderAction->execute($game->id, [
            'event_family' => 'pitching',
            'event_type' => 'pitch',
            'current_batter_id' => $batter->id,
            'current_pitcher_id' => $pitcher->id,
            'payload' => ['pitch_result' => 'strike'],
            'players' => [
                ['player_identity_id' => $pitcher->id, 'role' => 'pitcher'],
                ['player_identity_id' => $batter->id, 'role' => 'batter'],
            ]
        ], $user->id);

        // Event 3: Strikeout
        $recorderAction->execute($game->id, [
            'event_family' => 'plate_appearance',
            'event_type' => 'strikeout',
            'current_batter_id' => $batter->id,
            'current_pitcher_id' => $pitcher->id,
            'players' => [
                ['player_identity_id' => $pitcher->id, 'role' => 'pitcher'],
                ['player_identity_id' => $batter->id, 'role' => 'batter'],
            ]
        ], $user->id);

        // Event 4: Single (Hit)
        $recorderAction->execute($game->id, [
            'event_family' => 'plate_appearance',
            'event_type' => 'single',
            'current_batter_id' => $batter->id,
            'current_pitcher_id' => $pitcher->id,
            'players' => [
                ['player_identity_id' => $pitcher->id, 'role' => 'pitcher'],
                ['player_identity_id' => $batter->id, 'role' => 'batter'],
            ]
        ], $user->id);

        // Event 5: Base on Balls (Walk)
        $recorderAction->execute($game->id, [
            'event_family' => 'plate_appearance',
            'event_type' => 'walk',
            'current_batter_id' => $batter->id,
            'current_pitcher_id' => $pitcher->id,
            'players' => [
                ['player_identity_id' => $pitcher->id, 'role' => 'pitcher'],
                ['player_identity_id' => $batter->id, 'role' => 'batter'],
            ]
        ], $user->id);


        // ==========================================
        // 3. ASSERT: Verify resulting statistics
        // ==========================================

        // --- VERIFY BATTER STATS (Jonny Damon - Home Team context) ---
        // At-Bats (AB): Expected = 2 (Strikeout + Single are ABs; Walk is not an AB)
        $this->assertPlayerStat($game->id, $batter->id, 'AB', 2.00);

        // Hits (H): Expected = 1 (Single)
        $this->assertPlayerStat($game->id, $batter->id, 'H', 1.00);

        // Plate Appearances (PA): Expected = 3 (Strikeout + Single + Walk)
        $this->assertPlayerStat($game->id, $batter->id, 'PA', 3.00);

        // Walks (BB): Expected = 1 (Walk)
        $this->assertPlayerStat($game->id, $batter->id, 'BB', 1.00);

        // Strikeouts (SO): Expected = 1 (Strikeout)
        $this->assertPlayerStat($game->id, $batter->id, 'SO', 1.00);


        // --- VERIFY PITCHER STATS (Roger Clemens - Away Team context) ---
        // Total Pitches: Expected = 2 (1 Ball + 1 Strike)
        $this->assertPlayerStat($game->id, $pitcher->id, 'total_pitches', 2.00);

        // Strikes Thrown: Expected = 1
        $this->assertPlayerStat($game->id, $pitcher->id, 'strikes_thrown', 1.00);

        // Balls Thrown: Expected = 1
        $this->assertPlayerStat($game->id, $pitcher->id, 'balls_thrown', 1.00);

        // Batters Faced (BF): Expected = 3 (Strikeout + Single + Walk)
        $this->assertPlayerStat($game->id, $pitcher->id, 'BF', 3.00);

        // Hits Allowed: Expected = 1
        $this->assertPlayerStat($game->id, $pitcher->id, 'H_allowed', 1.00);

        // Walks Allowed: Expected = 1
        $this->assertPlayerStat($game->id, $pitcher->id, 'BB_allowed', 1.00);

        // Strikeouts Pitching: Expected = 1
        $this->assertPlayerStat($game->id, $pitcher->id, 'SO_pitching', 1.00);

        // IP Outs (Outs recorded on mound): Expected = 1 (from the strikeout)
        $this->assertPlayerStat($game->id, $pitcher->id, 'IP_outs', 1.00);


        // --- VERIFY PARALLEL TEAM STAT ROLLUPS ---
        // Home Team (Batting team totals)
        $this->assertTeamStat($game->id, $homeTeam->id, 'AB', 2.00);
        $this->assertTeamStat($game->id, $homeTeam->id, 'H', 1.00);
        $this->assertTeamStat($game->id, $homeTeam->id, 'PA', 3.00);
        $this->assertTeamStat($game->id, $homeTeam->id, 'BB', 1.00);
        $this->assertTeamStat($game->id, $homeTeam->id, 'SO', 1.00);

        // Away Team (Pitching team totals)
        $this->assertTeamStat($game->id, $awayTeam->id, 'total_pitches', 2.00);
        $this->assertTeamStat($game->id, $awayTeam->id, 'strikes_thrown', 1.00);
        $this->assertTeamStat($game->id, $awayTeam->id, 'balls_thrown', 1.00);
        $this->assertTeamStat($game->id, $awayTeam->id, 'BF', 3.00);
        $this->assertTeamStat($game->id, $awayTeam->id, 'H_allowed', 1.00);
        $this->assertTeamStat($game->id, $awayTeam->id, 'BB_allowed', 1.00);
        $this->assertTeamStat($game->id, $awayTeam->id, 'SO_pitching', 1.00);
        $this->assertTeamStat($game->id, $awayTeam->id, 'IP_outs', 1.00);

        // Ensure database state matches snapshots
        $this->assertDatabaseHas('game_state_snapshots', [
            'game_id' => $game->id,
            'inning_number' => 1,
            'half_inning' => 'top',
            'outs' => 1, // 1 out from strikeout, single & walk are runner placements
            'base_state' => '110', // runner on 1st & 2nd after walk follows single
        ]);
    }

    /**
     * Custom assertion to verify an individual player's statistical record.
     */
    protected function assertPlayerStat(string $gameId, string $playerId, string $key, float $value): void
    {
        $this->assertDatabaseHas('game_player_stats', [
            'game_id' => $gameId,
            'player_identity_id' => $playerId,
            'stat_key' => $key,
            'stat_value' => $value,
        ]);
    }

    /**
     * Custom assertion to verify a team's statistical rollup.
     */
    protected function assertTeamStat(string $gameId, string $teamId, string $key, float $value): void
    {
        $this->assertDatabaseHas('game_team_stats', [
            'game_id' => $gameId,
            'team_id' => $teamId,
            'stat_key' => $key,
            'stat_value' => $value,
        ]);
    }
}