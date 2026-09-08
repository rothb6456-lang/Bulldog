<?php

namespace App\Policies;

use App\Models\Ruleset;
use App\Models\User;

class RulesetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ruleset $ruleset): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Ruleset $ruleset): bool
    {
        return false;
    }

    public function delete(User $user, Ruleset $ruleset): bool
    {
        return false;
    }
}
