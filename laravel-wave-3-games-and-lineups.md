# Wave 3: Games, Rulesets, and Lineups Setup Guide

This guide contains the complete set of database migrations and Eloquent models for **Wave 3: Game Creation, Rulesets, and Live Lineups**. 

These schemas and models establish the structural foundation for the core game engine, enabling coaches to set up matches, define sport-specific rules, compile batting orders, and track defensive positions on the field.

---

## 🛠️ Step 1: Database Migrations

Create the following five migration files in your `database/migrations/` directory.

### Migration 1: `create_rulesets_table`
Run `php artisan make:migration create_rulesets_table` and replace the content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rulesets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sport_id')->index();
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('cascade');
            $table->string('name'); // e.g., '12U Travel Baseball Rules', 'Standard LL Softball'
            $table->json('config_json')->nullable(); // Store specific rules like DH, continuous batting order, innings limit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rulesets');
    }
};
```

### Migration 2: `create_games_table`
Run `php artisan make:migration create_games_table` and replace the content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('game_code')->unique()->index(); // e.g., GM-20260908-XYZ
            $table->uuid('sport_id')->index();
            $table->foreign('sport_id')->references('id')->on('sports')->onDelete('restrict');
            
            $table->uuid('home_team_id')->index();
            $table->foreign('home_team_id')->references('id')->on('teams')->onDelete('cascade');
            
            $table->uuid('away_team_id')->index();
            $table->foreign('away_team_id')->references('id')->on('teams')->onDelete('cascade');
            
            $table->timestamp('scheduled_at')->nullable();
            $table->string('location')->nullable();
            
            $table->uuid('ruleset_id')->nullable()->index();
            $table->foreign('ruleset_id')->references('id')->on('rulesets')->onDelete('set null');
            
            $table->string('status')->default('draft'); // draft, in_progress, review, finalized, corrected, suspended
            $table->uuid('ownership_team_id')->index(); // Defaults to home_team_id per MVP specifications
            $table->foreign('ownership_team_id')->references('id')->on('teams')->onDelete('cascade');
            
            $table->uuid('created_by_user_id')->nullable()->index();
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->timestamp('finalized_at')->nullable();
            $table->uuid('finalized_by_user_id')->nullable()->index();
            $table->foreign('finalized_by_user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
```

### Migration 3: `create_game_roster_entries_table`
Run `php artisan make:migration create_game_roster_entries_table` and replace the content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_roster_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            
            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            
            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('restrict');
            
            $table->uuid('team_membership_id')->nullable()->index();
            $table->foreign('team_membership_id')->references('id')->on('team_memberships')->onDelete('set null');
            
            $table->string('roster_status')->default('active'); // active, bench, injured, absent
            $table->boolean('eligible_to_play')->default(true);
            $table->timestamps();
            
            $table->unique(['game_id', 'player_identity_id'], 'game_player_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_roster_entries');
    }
};
```

### Migration 4: `create_lineup_entries_table`
Run `php artisan make:migration create_lineup_entries_table` and replace the content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lineup_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            
            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            
            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('restrict');
            
            $table->integer('batting_order_slot'); // 1 through 9+ (supporting continuous lineups)
            $table->string('lineup_status')->default('starter'); // starter, substitute, removed
            $table->timestamp('entered_at')->nullable();
            $table->timestamp('exited_at')->nullable();
            $table->timestamps();
            
            $table->unique(['game_id', 'team_id', 'batting_order_slot'], 'game_team_slot_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lineup_entries');
    }
};
```

### Migration 5: `create_defensive_assignments_table`
Run `php artisan make:migration create_defensive_assignments_table` and replace the content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('defensive_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            
            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            
            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('restrict');
            
            $table->string('position_code'); // P, C, 1B, 2B, 3B, SS, LF, CF, RF, EH, SUB
            $table->uuid('effective_from_event_id')->nullable()->index(); // Nullable initially before event logging
            $table->uuid('effective_to_event_id')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defensive_assignments');
    }
};
```

---

## 📦 Step 2: Eloquent Models

Create or update these files inside your `app/Models/` directory.

### 1. `app/Models/Ruleset.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruleset extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'sport_id',
        'name',
        'config_json',
    ];

    protected $casts = [
        'config_json' => 'array',
    ];

    /**
     * Get the sport associated with this ruleset.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Get games utilizing this ruleset configuration.
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
```

### 2. `app/Models/Game.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_code',
        'sport_id',
        'home_team_id',
        'away_team_id',
        'scheduled_at',
        'location',
        'ruleset_id',
        'status',
        'ownership_team_id',
        'created_by_user_id',
        'finalized_at',
        'finalized_by_user_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    /**
     * Get the sport played.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Get the home team.
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the away team.
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Get the ruleset applied.
     */
    public function ruleset(): BelongsTo
    {
        return $this->belongsTo(Ruleset::class);
    }

    /**
     * Get the roster entries loaded for this game.
     */
    public function rosterEntries(): HasMany
    {
        return $this->hasMany(GameRosterEntry::class);
    }

    /**
     * Get the lineup entries configured.
     */
    public function lineupEntries(): HasMany
    {
        return $this->hasMany(LineupEntry::class);
    }

    /**
     * Get the defensive assignments on the field.
     */
    public function defensiveAssignments(): HasMany
    {
        return $this->hasMany(DefensiveAssignment::class);
    }

    /**
     * Get the user who created this game record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
```

### 3. `app/Models/GameRosterEntry.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRosterEntry extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_id',
        'team_id',
        'player_identity_id',
        'team_membership_id',
        'roster_status',
        'eligible_to_play',
    ];

    protected $casts = [
        'eligible_to_play' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }

    public function teamMembership(): BelongsTo
    {
        return $this->belongsTo(TeamMembership::class);
    }
}
```

### 4. `app/Models/LineupEntry.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineupEntry extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_id',
        'team_id',
        'player_identity_id',
        'batting_order_slot',
        'lineup_status',
        'entered_at',
        'exited_at',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }
}
```

### 5. `app/Models/DefensiveAssignment.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefensiveAssignment extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_id',
        'team_id',
        'player_identity_id',
        'position_code',
        'effective_from_event_id',
        'effective_to_event_id',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }
}
```

---

## 🔄 Step 3: Registration with AuthServiceProvider

Remember to register these new models with their corresponding policies if you aren't relying entirely on auto-discovery:

```php
protected $policies = [
    // Existing policies...
    App\Models\Game::class => App\Policies\GamePolicy::class,
];
```
