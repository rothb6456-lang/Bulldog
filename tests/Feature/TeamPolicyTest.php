<?php

namespace Tests\Feature;

use App\Models\PlayerIdentity;
use App\Models\Sport;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\TeamPolicy as TeamPolicyModel;
use App\Models\TeamRoleAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function createSport(): Sport
    {
        return Sport::create([
            'code' => 'baseball',
            'name' => 'Baseball',
        ]);
    }

    protected function createTeam(): Team
    {
        return Team::create([
            'team_code' => 'TEAM-' . uniqid(),
            'name' => 'Test Team',
            'sport_id' => $this->createSport()->id,
            'created_by_user_id' => User::factory()->create()->id,
            'status' => 'active',
        ]);
    }

    protected function assignRole(User $user, Team $team, string $roleType): void
    {
        TeamRoleAssignment::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role_type' => $roleType,
            'granted_by_user_id' => $team->created_by_user_id,
        ]);
    }

    protected function addRosteredPlayer(Team $team, User $user): void
    {
        $playerIdentity = PlayerIdentity::create([
            'player_code' => 'P-' . uniqid(),
            'user_id' => $user->id,
            'claim_status' => 'claimed',
            'display_name' => $user->name,
            'birth_year' => 2010,
            'created_by_user_id' => $team->created_by_user_id,
        ]);

        TeamMembership::create([
            'team_id' => $team->id,
            'player_identity_id' => $playerIdentity->id,
            'membership_type' => 'player',
            'status' => 'active',
        ]);
    }

    public function test_team_admin_has_full_team_access(): void
    {
        $policy = new \App\Policies\TeamPolicy;
        $admin = User::factory()->create();
        $team = $this->createTeam();

        $this->assignRole($admin, $team, 'team_admin');

        $this->assertTrue($policy->view($admin, $team));
        $this->assertTrue($policy->update($admin, $team));
        $this->assertTrue($policy->assignRoles($admin, $team));
        $this->assertTrue($policy->manageRoster($admin, $team));
        $this->assertTrue($policy->scoreGame($admin, $team));
        $this->assertTrue($policy->viewGameData($admin, $team));
    }

    public function test_coach_can_manage_roster_and_score_game_but_not_assign_roles(): void
    {
        $policy = new \App\Policies\TeamPolicy;
        $coach = User::factory()->create();
        $team = $this->createTeam();

        $this->assignRole($coach, $team, 'coach');

        $this->assertTrue($policy->view($coach, $team));
        $this->assertTrue($policy->manageRoster($coach, $team));
        $this->assertTrue($policy->scoreGame($coach, $team));
        $this->assertTrue($policy->viewGameData($coach, $team));
        $this->assertFalse($policy->assignRoles($coach, $team));
        $this->assertFalse($policy->update($coach, $team));
    }

    public function test_scorekeeper_can_score_but_cannot_manage_roster(): void
    {
        $policy = new \App\Policies\TeamPolicy;
        $scorekeeper = User::factory()->create();
        $team = $this->createTeam();

        $this->assignRole($scorekeeper, $team, 'scorekeeper');

        $this->assertTrue($policy->view($scorekeeper, $team));
        $this->assertTrue($policy->scoreGame($scorekeeper, $team));
        $this->assertTrue($policy->viewGameData($scorekeeper, $team));
        $this->assertFalse($policy->manageRoster($scorekeeper, $team));
        $this->assertFalse($policy->assignRoles($scorekeeper, $team));
    }

    public function test_viewer_has_read_only_access(): void
    {
        $policy = new \App\Policies\TeamPolicy;
        $viewer = User::factory()->create();
        $team = $this->createTeam();

        $this->assignRole($viewer, $team, 'viewer');

        $this->assertTrue($policy->view($viewer, $team));
        $this->assertTrue($policy->viewGameData($viewer, $team));
        $this->assertFalse($policy->manageRoster($viewer, $team));
        $this->assertFalse($policy->scoreGame($viewer, $team));
        $this->assertFalse($policy->assignRoles($viewer, $team));
    }

    public function test_rostered_player_has_team_and_game_read_access(): void
    {
        $policy = new \App\Policies\TeamPolicy;
        $player = User::factory()->create();
        $team = $this->createTeam();

        $this->addRosteredPlayer($team, $player);

        $this->assertTrue($policy->view($player, $team));
        $this->assertTrue($policy->viewGameData($player, $team));
        $this->assertFalse($policy->manageRoster($player, $team));
        $this->assertFalse($policy->scoreGame($player, $team));
    }
}
