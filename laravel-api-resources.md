# Laravel API Resources for Bulldog Statbook

To ensure that your API controllers return clean, structured, and secure JSON responses matching your visual prototype (such as `team-detail.html`), we need to implement Laravel **API Resources**. 

These resources act as a presentation layer that transforms your Eloquent Models and relationships into the exact JSON contracts your frontend expects, while preventing sensitive fields (like hashed passwords or internal database tracking columns) from leaking [155, 231].

Create the following four files in your GitHub Codespace.

---

### 1. Player Identity Resource
This transforms the `PlayerIdentity` model. It is crucial because the roster memberships reference player records, and we need to securely expose identity codes and claim statuses [59, 132].

Create a new file at **`app/Http/Resources/Identity/PlayerIdentityResource.php`**:

```php
<?php

namespace App\Http\Resources\Identity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerIdentityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'player_code' => $this->player_code, // e.g., PLY-TZNJGR
            'display_name' => $this->display_name,
            'birth_year' => $this->birth_year,
            'claim_status' => $this->claim_status, // unclaimed, pending, claimed, disputed
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

---

### 2. Team Role Assignment Resource
This transforms the roles assigned on a team (e.g., Coach, Admin, Scorekeeper) to match your team detail page's "Roles" view [1, 137].

Create a new file at **`app/Http/Resources/Teams/TeamRoleAssignmentResource.php`**:

```php
<?php

namespace App\Http\Resources\Teams;

use App\Http\Resources\Auth\MeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamRoleAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role_type' => $this->role_type, // team_admin, coach, scorekeeper, viewer
            'user' => [
                'id' => $this->user_id,
                'email' => $this->user?->email,
                'display_name' => $this->user?->profile?->display_name,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

---

### 3. Roster Entry Resource
This transforms the `TeamMembership` model (your team roster slot). It automatically maps field positions as a native JSON array and nests the secure `PlayerIdentityResource` [136, 205].

Create a new file at **`app/Http/Resources/Teams/RosterEntryResource.php`**:

```php
<?php

namespace App\Http\Resources\Teams;

use App\Http\Resources\Identity\PlayerIdentityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RosterEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jersey_number' => $this->jersey_number,
            'membership_type' => $this->membership_type, // player, coach, manager, assistant
            'positions' => $this->positions_json ?? [], // Automatically cast as an array/list
            'status' => $this->status, // active, inactive
            'player_identity' => new PlayerIdentityResource($this->whenLoaded('playerIdentity')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

---

### 4. Team Resource
This is the master representation of your team context. It transforms your team metadata, includes the sport name, and conditionally nests the team roster and assigned roles if they have been eager-loaded in the controller query [216, 257].

Create a new file at **`app/Http/Resources/Teams/TeamResource.php`**:

```php
<?php

namespace App\Http\Resources\Teams;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_code' => $this->team_code, // e.g., TM-SPRINGFIEL-YLYI
            'name' => $this->name,
            'age_group' => $this->age_group, // e.g., 12U
            'season_label' => $this->season_label, // e.g., Fall 2026
            'status' => $this->status, // active, archived
            'sport' => [
                'id' => $this->sport_id,
                'code' => $this->sport?->code, // e.g., baseball
                'name' => $this->sport?->name, // e.g., Baseball
            ],
            // Conditionally load the roster and role assignments only if eager-loaded
            'roster' => RosterEntryResource::collection($this->whenLoaded('memberships')),
            'roles' => TeamRoleAssignmentResource::collection($this->whenLoaded('roleAssignments')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

---

### ⚡ Eager-Loading in Your Controllers for Maximum Performance

To prevent N+1 database queries when using these resources, update your controller queries so that all nested models are retrieved in a single database round-trip.

#### Example: Fetching Team Details in `TeamController.php`
```php
use App\Http\Resources\Teams\TeamResource;
use App\Models\Team;

public function show(Request $request, Team $team)
{
    $this->authorize('view', $team);

    // Eager-load 'sport', 'memberships.playerIdentity', and 'roleAssignments.user.profile'
    $team->load([
        'sport',
        'memberships.playerIdentity',
        'roleAssignments.user.profile'
    ]);

    return new TeamResource($team);
}
```
