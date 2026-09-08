<?php

namespace App\Providers;

use App\Models\DefensiveAssignment;
use App\Models\Game;
use App\Models\GameRosterEntry;
use App\Models\LineupEntry;
use App\Models\Ruleset;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Policies\DefensiveAssignmentPolicy;
use App\Policies\GamePolicy;
use App\Policies\GameRosterEntryPolicy;
use App\Policies\LineupEntryPolicy;
use App\Policies\RulesetPolicy;
use App\Policies\TeamPolicy;
use App\Policies\TeamMembershipPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        TeamMembership::class => TeamMembershipPolicy::class,
        Ruleset::class => RulesetPolicy::class,
        Game::class => GamePolicy::class,
        GameRosterEntry::class => GameRosterEntryPolicy::class,
        LineupEntry::class => LineupEntryPolicy::class,
        DefensiveAssignment::class => DefensiveAssignmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
