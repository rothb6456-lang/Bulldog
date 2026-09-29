<?php

namespace Tests\Feature;

use App\Actions\Games\CreatePracticeGameAction;
use App\Actions\Teams\CreateTeamAction;
use App\Models\{User, Sport, Team, PlayerIdentity, GuardianRelationship, Game, GameEvent, GameStateSnapshot, CareerAggregate, AchievementAward, XpLedger, Exercise, TrainingPhase};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EcosystemBetaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([\Database\Seeders\SportSeeder::class, \Database\Seeders\RulesetSeeder::class, \Database\Seeders\SystemBadgeSeeder::class]);
    }

    public function test_hub_previews_and_team_creation_are_wired(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Launch Momentum');
        $this->get('/nutrition')->assertOk()->assertSee('Yogurt recovery bowl');
        $this->get('/merch')->assertOk()->assertSee('NOT FOR SALE');
        $this->post('/teams', ['name' => 'Beta team', 'sport_id' => Sport::first()->id])->assertRedirect();
        $team = Team::where('name', 'Beta team')->firstOrFail();
        $this->assertDatabaseHas('team_role_assignments', ['team_id' => $team->id, 'user_id' => $user->id, 'role_type' => 'coach']);
        $this->get('/teams/'.$team->id)->assertOk();
        $this->actingAs(User::factory()->create())->get('/teams/'.$team->id)->assertForbidden();
    }

    public function test_guardian_requires_acceptance_and_separate_admin_confirmation(): void
    {
        $coach = User::factory()->create();
        $guardian = User::factory()->create();
        $other = User::factory()->create();
        $team = app(CreateTeamAction::class)->execute(['name' => 'Private youth team', 'sport_id' => Sport::first()->id], $coach->id);
        $this->actingAs($coach)->post('/teams/'.$team->id.'/roster', ['display_name' => 'Private youth', 'birth_year' => now()->year - 12])->assertRedirect();
        $player = $team->memberships()->first()->playerIdentity;
        $this->post('/teams/'.$team->id.'/guardians', ['player_identity_id' => $player->id, 'email' => $guardian->email])->assertRedirect();
        $relationship = GuardianRelationship::firstOrFail();
        $this->post('/guardians/'.$relationship->id.'/confirm')->assertStatus(422);
        $this->actingAs($other)->post('/guardians/'.$relationship->id.'/accept')->assertForbidden();
        $this->actingAs($guardian)->get('/players/'.$player->id)->assertForbidden();
        $this->get('/guardians')->assertOk()->assertDontSee('Private youth</', false);
        $this->post('/guardians/'.$relationship->id.'/accept')->assertRedirect();
        $this->get('/players/'.$player->id)->assertForbidden();
        $this->post('/guardians/'.$relationship->id.'/confirm')->assertForbidden();
        $this->actingAs($coach)->post('/guardians/'.$relationship->id.'/confirm')->assertRedirect();
        $this->actingAs($guardian)->get('/players/'.$player->id)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->actingAs($coach)->post('/guardians/'.$relationship->id.'/revoke')->assertRedirect();
        $this->actingAs($guardian)->get('/players/'.$player->id)->assertForbidden();
    }

    public function test_launch_code_is_short_lived_single_use_and_account_bound(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/momentum/launch')->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_FRAGMENT), $fragment);
        $code = $fragment['bulldog_launch'];
        $this->assertDatabaseMissing('momentum_launch_codes', ['code_hash' => $code]);
        $this->postJson('/api/v1/auth/exchange', ['code' => $code])->assertOk()->assertJsonPath('user.id', $user->id);
        $this->postJson('/api/v1/auth/exchange', ['code' => $code])->assertStatus(422);
        $response = $this->post('/momentum/launch');
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_FRAGMENT), $fragment);
        $this->travel(2)->minutes();
        $this->postJson('/api/v1/auth/exchange', ['code' => $fragment['bulldog_launch']])->assertStatus(422);
    }

    public function test_practice_game_scores_both_halves_but_never_changes_real_history_or_awards(): void
    {
        $user = User::factory()->create();
        $game = app(CreatePracticeGameAction::class)->execute($user, Sport::first());
        $this->actingAs($user)->get('/games/'.$game->id.'/score')->assertOk();
        $away = $game->rosterEntries()->where('team_id', $game->away_team_id)->first()->player_identity_id;
        $home = $game->rosterEntries()->where('team_id', $game->home_team_id)->first()->player_identity_id;
        $play = fn ($type, $batter, $pitcher) => ['event_family' => 'plate_appearance', 'event_type' => $type, 'players' => [['player_identity_id' => $batter, 'role' => 'batter'], ['player_identity_id' => $pitcher, 'role' => 'pitcher']]];
        $this->post('/games/'.$game->id.'/score', $play('home_run', $away, $home))->assertRedirect();
        for ($i = 0; $i < 3; $i++) { $this->post('/games/'.$game->id.'/score', $play('strikeout', $away, $home))->assertRedirect(); }
        $last = GameEvent::where('game_id', $game->id)->orderByDesc('sequence_number')->first();
        $this->assertSame('top', $last->half_inning);
        $this->assertSame('bottom', GameStateSnapshot::where('game_id', $game->id)->orderByDesc('sequence_number')->first()->half_inning);
        $this->post('/games/'.$game->id.'/score', $play('home_run', $home, $away))->assertRedirect();
        $this->assertDatabaseHas('game_player_stats', ['player_identity_id' => $away, 'team_id' => $game->away_team_id, 'stat_key' => 'HR', 'stat_value' => 1]);
        $this->assertDatabaseHas('game_player_stats', ['player_identity_id' => $home, 'team_id' => $game->home_team_id, 'stat_key' => 'HR', 'stat_value' => 1]);
        $this->post('/games/'.$game->id.'/finalize')->assertRedirect();
        app(\App\Actions\History\RecompilePlayerHistoryAction::class)->execute($away);
        $this->assertSame(0, CareerAggregate::count());
        $this->assertSame(0, AchievementAward::count());
        $this->assertSame(0, XpLedger::count());
        $this->postJson('/api/games/'.$game->id.'/events', $play('single', $home, $away))->assertStatus(422);
        $this->get('/games/'.$game->id.'/score')->assertOk()->assertSee('FINAL');
    }

    public function test_seeders_and_practice_rosters_are_repeatable(): void
    {
        $this->seed([\Database\Seeders\SportSeeder::class, \Database\Seeders\RulesetSeeder::class]);
        $this->assertDatabaseCount('sports', 2);
        $this->assertDatabaseCount('rulesets', 5);
        $user = User::factory()->create();
        app(CreatePracticeGameAction::class)->execute($user, Sport::first());
        app(CreatePracticeGameAction::class)->execute($user, Sport::first());
        $this->assertDatabaseCount('teams', 2);
        $this->assertDatabaseCount('player_identities', 18);
        $this->assertDatabaseCount('games', 2);
    }

    public function test_pregame_forms_and_profile_sync_are_connected(): void
    {
        $user = User::factory()->create();
        $game = app(CreatePracticeGameAction::class)->execute($user, Sport::first());
        $game->update(['status' => 'draft']);
        $this->actingAs($user)->get('/games/create')->assertOk();
        $this->get('/games/'.$game->id)->assertOk();
        $player = $game->rosterEntries()->where('team_id', $game->home_team_id)->first()->player_identity_id;
        $this->post('/games/'.$game->id.'/lineup/add', ['player_identity_id' => $player])->assertRedirect();
        $entry = $game->lineupEntries()->where('player_identity_id', $player)->firstOrFail();
        $this->put('/games/'.$game->id.'/lineup', ['slots' => [$entry->id => ['order' => 1, 'lineup_status' => 'starter']]])->assertRedirect();
        $this->put('/games/'.$game->id.'/defense', ['defense' => ['P' => $player]])->assertRedirect();
        $this->assertDatabaseHas('defensive_assignments', ['game_id' => $game->id, 'player_identity_id' => $player, 'position_code' => 'P']);
        $this->postJson('/api/v1/training/profile', ['experience_level' => 'beginner', 'goals' => [['title' => 'Build strength']]])->assertOk();
        $identity = $user->fresh()->playerIdentity;
        $this->get('/players/'.$identity->id)->assertOk()->assertSee('Build strength');
    }

    public function test_sync_retry_is_idempotent_and_cannot_write_another_identity(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::create(['canonical_name' => 'Beta canonical squat']);
        $payload = ['session_id' => 'local-session-1', 'session_date' => now()->toDateString(), 'workout_name' => 'QA workout', 'sets' => [['exercise_id' => $exercise->id, 'exercise_name' => $exercise->canonical_name, 'set_number' => 1, 'weight_lbs' => 20, 'reps' => 8]]];
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/training/sessions', $payload)->assertCreated();
        $this->postJson('/api/v1/training/sessions', $payload)->assertCreated();
        $this->assertDatabaseCount('training_sessions', 1);
        $this->assertDatabaseCount('training_sets', 1);
        $this->assertDatabaseHas('training_sets', ['weight_lbs' => 20, 'reps' => 8]);
        $other = app(\App\Actions\Training\ResolveTrainingIdentity::class)->execute(User::factory()->create());
        $payload['player_identity_id'] = $other->id;
        $this->postJson('/api/v1/training/sessions', $payload)->assertNotFound();
    }

    public function test_weekly_consistency_awards_once_per_week(): void
    {
        $user = User::factory()->create();
        $player = app(\App\Actions\Training\ResolveTrainingIdentity::class)->execute($user);
        $phase = TrainingPhase::create(['player_identity_id' => $player->id, 'phase_number' => 1, 'name' => 'Beta phase', 'start_date' => now()->startOfWeek(), 'sessions_per_week' => 2]);
        $exercise = Exercise::create(['canonical_name' => 'Consistency squat']);
        $payload = ['session_id' => 'one', 'session_date' => now()->toDateString(), 'workout_name' => 'QA workout', 'sets' => [['exercise_name' => $exercise->canonical_name, 'set_number' => 1, 'reps' => 8]]];
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/training/sessions', $payload)->assertCreated();
        $payload['session_id'] = 'two';
        $this->postJson('/api/v1/training/sessions', $payload)->assertCreated();
        $this->postJson('/api/v1/training/sessions', $payload)->assertCreated();
        $this->assertDatabaseCount('achievement_awards', 1);
        $this->assertDatabaseCount('xp_ledger', 1);
    }
}
