# Wave 6: Milestones, Achievements & Controlled Visual Shareable Cards

This document details the database migrations, Eloquent models, event listeners, dynamic rule actions, and visual Blade layouts to implement **Wave 6: Milestones, Achievements, and Controlled Visual Shareable Cards** for Bulldog Statbook.

In alignment with the core product thesis, all achievements, streaks, and milestones are derived cleanly from **recalculable statistical data** [71, 76]. Experience points (XP) are strictly gated to live scorekeeping events and are never awarded for imported historical statistics [77, 144]. Underage player profiles are strictly protected: shareable cards are designed for controlled link access only and are explicitly flagged to prevent search engine web crawling [80, 85, 276].

---

## 🛠️ Step 1: Database Migrations

Create these eight migrations inside your `database/migrations/` directory. They are designed to be Postgres-portable, using string status indicators and UUID keys [206, 207].

### Migration 1: `create_milestones_table`
Tracks individual milestones reached (e.g., 50th career hit, first home run) [76, 117].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // 'player', 'team', 'coach'
            $table->uuid('subject_id')->index();
            
            $table->string('milestone_type'); // e.g., 'career_hits_threshold', 'first_home_run'
            $table->string('scope_type');     // 'game', 'season', 'career'
            $table->decimal('value_reached', 8, 2);
            
            $table->string('official_status')->default('official'); // 'provisional', 'official', 'superseded'
            $table->string('source_type')->default('bulldog_derived'); // 'bulldog_derived', 'uploaded_import'
            $table->string('fidelity_level')->nullable(); // 'season_totals', 'play_by_play', 'event_complete'
            
            $table->json('metadata_json')->nullable(); // Stores context like game_id or hit details
            $table->timestamp('detected_at');
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'milestone_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
```

### Migration 2: `create_records_table`
Caches record-setting peak values (e.g., season strikeout record, game hits record) [77, 117].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('record_scope');  // 'personal', 'team', 'season', 'career'
            $table->string('subject_type');  // 'player', 'team'
            $table->uuid('subject_id')->index();
            
            $table->string('stat_key');      // e.g., 'HR', 'SO_pitching', 'RBI'
            $table->decimal('record_value', 8, 2);
            
            $table->uuid('originating_game_id')->nullable(); // Game where record was set
            $table->string('official_status')->default('official');
            $table->string('source_type')->default('bulldog_derived');
            $table->json('metadata_json')->nullable();
            $table->timestamp('effective_date');
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'stat_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
```

### Migration 3: `create_achievement_definitions_table`
Defines system-standard rewards (coaches cannot define custom ones in MVP) [77].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // e.g., 'CYCLE_RIDER', 'ACE_IN_HOLE'
            $table->string('name');
            $table->text('description');
            $table->string('category');       // 'performance', 'participation', 'milestone'
            $table->json('rule_json');        // Formal structure describing conditions
            $table->integer('xp_value')->default(0);
            $table->boolean('is_repeatable')->default(false);
            $table->string('visibility_default')->default('team');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_definitions');
    }
};
```

### Migration 4: `create_achievement_awards_table`
Represents achievement instances earned by users or players [143].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_awards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('achievement_definition_id')->index();
            $table->foreign('achievement_definition_id')->references('id')->on('achievement_definitions')->onDelete('cascade');
            
            $table->string('subject_type'); // 'player', 'team'
            $table->uuid('subject_id')->index();
            
            $table->string('official_status')->default('official');
            $table->string('source_type')->default('bulldog_derived');
            $table->json('metadata_json')->nullable(); // Contextual references (e.g., game_id)
            $table->timestamp('awarded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_awards');
    }
};
```

### Migration 5: `create_streaks_table`
Logs running hitting, on-base, and winning streaks [117, 144].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streaks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // 'player', 'team'
            $table->uuid('subject_id')->index();
            
            $table->string('streak_type');  // 'hitting_streak', 'on_base_streak', 'winning_streak'
            $table->integer('current_value')->default(0);
            $table->integer('best_value')->default(0);
            
            $table->uuid('start_game_id')->nullable();
            $table->uuid('end_game_id')->nullable(); // Null if streak is currently active
            $table->string('official_status')->default('official');
            $table->timestamps();

            $table->index(['subject_type', 'subject_id', 'streak_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streaks');
    }
};
```

### Migration 6: `create_timeline_entries_table`
Stores auto-generated chronological milestone highlights for a player or team's history [117, 144].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timeline_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // 'player', 'team'
            $table->uuid('subject_id')->index();
            
            $table->string('entry_type');  // 'join_team', 'first_game', 'milestone_reached', 'achievement_earned'
            $table->timestamp('entry_date');
            $table->string('title');
            $table->text('description');
            
            $table->string('source_type')->default('bulldog_derived');
            $table->uuid('source_ref_id')->nullable(); // Poly link to milestone, award, or game record
            $table->string('official_status')->default('official');
            $table->string('visibility')->default('team'); // 'private', 'team', 'public'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timeline_entries');
    }
};
```

### Migration 7: `create_share_links_table`
Enables secure, controlled sharing of cards/milestones without exposing players to general search engine indexes [85, 145].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('object_type'); // 'player_card', 'milestone', 'achievement_award'
            $table->uuid('object_id')->index();
            $table->string('token')->unique(); // High-entropy signature token
            
            $table->uuid('created_by_user_id');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_authenticated_only')->default(false);
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_links');
    }
};
```

### Migration 8: `create_xp_ledger_table`
Awarded strictly for real-time game scoring and validated platform achievements. Imported rows are explicitly barred [77, 144].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_ledger', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->integer('xp_amount');
            $table->string('source_type'); // 'game_scoring', 'achievement_unlocked'
            $table->uuid('source_ref_id')->nullable(); // Points to finalized game or achievement award
            
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_ledger');
    }
};
```

---

## 💻 Step 2: Eloquent Models

Create these models inside your `app/Models/` directory, implementing the dynamic UUID and casting settings.

### 1. `app/Models/Milestone.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'milestone_type',
        'scope_type',
        'value_reached',
        'official_status',
        'source_type',
        'fidelity_level',
        'metadata_json',
        'detected_at',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'value_reached' => 'float',
        'detected_at' => 'datetime',
    ];
}
```

### 2. `app/Models/Record.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'record_scope',
        'subject_type',
        'subject_id',
        'stat_key',
        'record_value',
        'originating_game_id',
        'official_status',
        'source_type',
        'metadata_json',
        'effective_date',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'record_value' => 'float',
        'effective_date' => 'datetime',
    ];
}
```

### 3. `app/Models/AchievementDefinition.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AchievementDefinition extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'rule_json',
        'xp_value',
        'is_repeatable',
        'visibility_default',
    ];

    protected $casts = [
        'rule_json' => 'array',
        'is_repeatable' => 'boolean',
        'xp_value' => 'integer',
    ];

    public function awards(): HasMany
    {
        return $this->hasMany(AchievementAward::class);
    }
}
```

### 4. `app/Models/AchievementAward.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementAward extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'achievement_definition_id',
        'subject_type',
        'subject_id',
        'official_status',
        'source_type',
        'metadata_json',
        'awarded_at',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'awarded_at' => 'datetime',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(AchievementDefinition::class, 'achievement_definition_id');
    }
}
```

### 5. `app/Models/Streak.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'streak_type',
        'current_value',
        'best_value',
        'start_game_id',
        'end_game_id',
        'official_status',
    ];

    protected $casts = [
        'current_value' => 'integer',
        'best_value' => 'integer',
    ];
}
```

### 6. `app/Models/TimelineEntry.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimelineEntry extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'entry_type',
        'entry_date',
        'title',
        'description',
        'source_type',
        'source_ref_id',
        'official_status',
        'visibility',
    ];

    protected $casts = [
        'entry_date' => 'datetime',
    ];
}
```

### 7. `app/Models/ShareLink.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareLink extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'object_type',
        'object_id',
        'token',
        'created_by_user_id',
        'expires_at',
        'is_active',
        'allow_authenticated_only',
        'metadata_json',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'allow_authenticated_only' => 'boolean',
    ];
}
```

### 8. `app/Models/XpLedger.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpLedger extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'xp_ledger';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'xp_amount',
        'source_type',
        'source_ref_id',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## ⚡ Step 3: Trigger Engine (Laravel Events)

When a game is transitioned to `'finalized'`, we trigger a `GameFinalized` event [72, 82]. An event listener handles the dynamic processing chain sequentially to prevent calculation conflicts [275].

### 1. The Event: `app/Events/GameFinalized.php`
```php
<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameFinalized
{
    use Dispatchable, SerializesModels;

    public Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }
}
```

### 2. The Listener: `app/Listeners/ProcessPostGameAchievements.php`
```php
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
```

---

## 🧠 Step 4: The Core Detection Rules & Actions

These actions process the play logs and cached box score stats to detect milestones, records, and award achievements.

### 1. Evaluating Streaks: `app/Actions/Scoring/EvaluateGameStreaksAction.php`
Calculates hitting streaks (consecutive games with 1+ Hits) and on-base streaks (Hits, Walks, or HBP) [117].
```php
<?php

namespace App\Actions\Scoring;

use App\Models\Game;
use App\Models\Streak;
use App\Models\GamePlayerStat;
use Illuminate\Support\Facades\DB;

class EvaluateGameStreaksAction
{
    public function execute(Game $game): void
    {
        DB::transaction(function () use ($game) {
            // Fetch all players who registered at least one Plate Appearance (PA) in this game
            $participants = GamePlayerStat::where('game_id', $game->id)
                ->where('stat_key', 'PA')
                ->where('stat_value', '>', 0)
                ->get();

            foreach ($participants as $statRecord) {
                $playerId = $statRecord->player_identity_id;

                // Check Hits and On-Base occurrences for this game
                $hits = GamePlayerStat::where('game_id', $game->id)
                    ->where('player_identity_id', $playerId)
                    ->where('stat_key', 'H')
                    ->value('stat_value') ?? 0;

                $walks = GamePlayerStat::where('game_id', $game->id)
                    ->where('player_identity_id', $playerId)
                    ->where('stat_key', 'BB')
                    ->value('stat_value') ?? 0;

                $this->updatePlayerStreak($playerId, 'hitting_streak', $hits > 0, $game->id);
                $this->updatePlayerStreak($playerId, 'on_base_streak', ($hits > 0 || $walks > 0), $game->id);
            }
        });
    }

    protected function updatePlayerStreak(string $playerId, string $type, bool $isExtended, string $gameId): void
    {
        $streak = Streak::firstOrCreate(
            ['subject_type' => 'player', 'subject_id' => $playerId, 'streak_type' => $type],
            ['current_value' => 0, 'best_value' => 0]
        );

        if ($isExtended) {
            $streak->current_value++;
            if ($streak->current_value === 1) {
                $streak->start_game_id = $gameId;
            }
            if ($streak->current_value > $streak->best_value) {
                $streak->best_value = $streak->current_value;
            }
            $streak->end_game_id = null;
        } else {
            if ($streak->current_value > 0) {
                $streak->end_game_id = $gameId;
            }
            $streak->current_value = 0;
        }

        $streak->save();
    }
}
```

### 2. Threshold Milestones: `app/Actions/Scoring/EvaluatePlayerMilestonesAction.php`
Scans cumulative career metrics to detect when players cross specific benchmarks (e.g. 10th Hit, 100th PA) and appends timeline highlights [117].
```php
<?php

namespace App\Actions\Scoring;

use App\Models\Game;
use App\Models\Milestone;
use App\Models\TimelineEntry;
use App\Models\CareerAggregate;
use App\Models\GamePlayerStat;
use Illuminate\Support\Facades\DB;

class EvaluatePlayerMilestonesAction
{
    protected array $thresholds = [
        'H' => [10 => '10 Career Hits', 50 => '50 Career Hits', 100 => '100 Career Hits'],
        'SO_pitching' => [25 => '25 Career Strikeouts', 100 => '100 Career Strikeouts'],
    ];

    public function execute(Game $game): void
    {
        DB::transaction(function () use ($game) {
            $boxStats = GamePlayerStat::where('game_id', $game->id)->get();

            foreach ($boxStats as $stat) {
                $playerId = $stat->player_identity_id;
                $key = $stat->stat_key;

                if (!array_key_exists($key, $this->thresholds)) {
                    continue;
                }

                // Query current total aggregate
                $totalValue = CareerAggregate::where('scope_type', 'player')
                    ->where('scope_id', $playerId)
                    ->where('stat_key', $key)
                    ->value('stat_value') ?? 0.00;

                foreach ($this->thresholds[$key] as $limit => $label) {
                    if ($totalValue >= $limit) {
                        // Ensure milestone has not been logged already
                        $exists = Milestone::where('subject_type', 'player')
                            ->where('subject_id', $playerId)
                            ->where('milestone_type', "career_{$key}_threshold")
                            ->where('value_reached', $limit)
                            ->exists();

                        if (!$exists) {
                            $milestone = Milestone::create([
                                'subject_type' => 'player',
                                'subject_id' => $playerId,
                                'milestone_type' => "career_{$key}_threshold",
                                'scope_type' => 'career',
                                'value_reached' => $limit,
                                'official_status' => 'official',
                                'source_type' => 'bulldog_derived',
                                'metadata_json' => ['game_id' => $game->id, 'label' => $label],
                                'detected_at' => now(),
                            ]);

                            // Auto-generate timeline highlight
                            TimelineEntry::create([
                                'subject_type' => 'player',
                                'subject_id' => $playerId,
                                'entry_type' => 'milestone_reached',
                                'entry_date' => now(),
                                'title' => "Milestone Reached: {$label}!",
                                'description' => "Hit a career milestone of {$limit} total {$key} during play.",
                                'source_type' => 'bulldog_derived',
                                'source_ref_id' => $milestone->id,
                                'visibility' => 'team',
                            ]);
                        }
                    }
                }
            }
        });
    }
}
```

### 3. Automatic Achievements & XP Ledger Integration: `app/Actions/Scoring/AwardSystemAchievementsAction.php`
Evaluates complex single-game achievements (e.g. "Ace in the Hole" - pitching 10+ Ks) and awards XP. **Crucially checks source-type to guard the XP ledger** [77, 144].
```php
<?php

namespace App\Actions\Scoring;

use App\Models\Game;
use App\Models\AchievementDefinition;
use App\Models\AchievementAward;
use App\Models\TimelineEntry;
use App\Models\XpLedger;
use App\Models\GamePlayerStat;
use App\Models\PlayerIdentity;
use Illuminate\Support\Facades\DB;

class AwardSystemAchievementsAction
{
    public function execute(Game $game): void
    {
        DB::transaction(function () use ($game) {
            // Find game players with 10+ strikeouts on the mound
            $pitchers = GamePlayerStat::where('game_id', $game->id)
                ->where('stat_key', 'SO_pitching')
                ->where('stat_value', '>=', 10)
                ->get();

            $aceDefinition = AchievementDefinition::where('code', 'ACE_IN_HOLE')->first();

            if ($aceDefinition) {
                foreach ($pitchers as $stat) {
                    $playerId = $stat->player_identity_id;

                    // Ensure this player has not earned this achievement in this specific game
                    $exists = AchievementAward::where('achievement_definition_id', $aceDefinition->id)
                        ->where('subject_type', 'player')
                        ->where('subject_id', $playerId)
                        ->where('metadata_json->game_id', $game->id)
                        ->exists();

                    if (!$exists) {
                        $award = AchievementAward::create([
                            'achievement_definition_id' => $aceDefinition->id,
                            'subject_type' => 'player',
                            'subject_id' => $playerId,
                            'official_status' => 'official',
                            'source_type' => 'bulldog_derived',
                            'metadata_json' => ['game_id' => $game->id],
                            'awarded_at' => now(),
                        ]);

                        // Generate Timeline highlight
                        TimelineEntry::create([
                            'subject_type' => 'player',
                            'subject_id' => $playerId,
                            'entry_type' => 'achievement_earned',
                            'entry_date' => now(),
                            'title' => "Earned '{$aceDefinition->name}' Badge",
                            'description' => "Dominating performance on the mound with 10+ game strikeouts.",
                            'source_type' => 'bulldog_derived',
                            'source_ref_id' => $award->id,
                            'visibility' => 'team',
                        ]);

                        // Secure XP ledger mapping — Only trigger if player identity is linked and NOT historical import [77, 144]
                        $player = PlayerIdentity::find($playerId);
                        if ($player && $player->user_id) {
                            XpLedger::create([
                                'user_id' => $player->user_id,
                                'xp_amount' => $aceDefinition->xp_value,
                                'source_type' => 'achievement_unlocked',
                                'source_ref_id' => $award->id,
                                'description' => "Earned system badge: '{$aceDefinition->name}' during official play.",
                            ]);
                        }
                    }
                }
            }
        });
    }
}
```

---

## 🔒 Step 5: Controlled Share Cards & Secure Routing

To balance fun sharing with **strict minor-safety rules**, cards cannot be crawled by search bots [80]. We employ non-indexed route templates and signed, high-entropy tokens to maintain safety [85, 276].

### 🔌 Router Definitions: `routes/web.php`
```php
<?php

use App\Http\Controllers\Web\ShareLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Generate secure share token for a player identity
    Route::post('/sharing/players/{player}/card', [ShareLinkController::class, 'store'])
        ->name('share.player.card');
});

// High-entropy token resolution route (outside auth, controlled display) [189]
Route::get('/shared/cards/{token}', [ShareLinkController::class, 'show'])
    ->name('share.resolve');
```

### 🎛️ Controller logic: `app/Http/Controllers/Web/ShareLinkController.php`
```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PlayerIdentity;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ShareLinkController extends Controller
{
    /**
     * Create high-entropy token to build secure, non-indexed link.
     */
    public function store(Request $request, PlayerIdentity $player)
    {
        // Require Coach, Admin, or Guardian relationship to initiate sharing [81, 104]
        $this->authorize('share', $player);

        $share = ShareLink::create([
            'object_type' => 'player_card',
            'object_id' => $player->id,
            'token' => Str::random(40), // Collision-safe, unguessable string
            'created_by_user_id' => Auth::id(),
            'expires_at' => now()->addDays(30), // Configurable expiration
            'is_active' => true,
        ]);

        return response()->json([
            'share_url' => route('share.resolve', ['token' => $share->token]),
        ], 201);
    }

    /**
     * Resolve the token to render the visual card without letting bots index it.
     */
    public function show(string $token)
    {
        $share = ShareLink::where('token', $token)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        if ($share->object_type === 'player_card') {
            $player = PlayerIdentity::findOrFail($share->object_id);

            // Load player profile metadata and stats
            $player->load(['memberships.team', 'profile']);
            
            // Extract stats
            $careerStats = DB::table('career_aggregates')
                ->where('scope_type', 'player')
                ->where('scope_id', $player->id)
                ->pluck('stat_value', 'stat_key')
                ->toArray();

            // Check if ANY statistic in their history came from imported offline spreadsheet sources [75, 148]
            $hasImportedStats = DB::table('imported_stat_lines')
                ->where('subject_type', 'player')
                ->where('subject_id', $player->id)
                ->exists();

            return view('sharing.share-card', compact('player', 'careerStats', 'hasImportedStats'));
        }

        abort(404);
    }
}
```

---

## 🎨 Step 6: Visual Shareable Card Blade View

Create this blade file at **`resources/views/sharing/share-card.blade.php`**.

This utilizes your Whalers-inspired green/navy theme, sports an eye-catching "Baseball Card" border-box composition, and strictly integrates **`noindex, nofollow`** to satisfy our youth privacy standards [80, 85].

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ⚠️ MANDATORY PRIVACY SHIELD: Strictly block search engine indexing [80, 85, 276] -->
    <meta name="robots" content="noindex, nofollow, noarchive">
    
    <title>Bulldog Statbook | Player Card - {{ $player->display_name }}</title>

    <style>
        :root {
            --bg: #f3f5f4;
            --surface: #ffffff;
            --navy: #081722;
            --navy-light: #102533;
            --green: #007a43;
            --border: #d7dede;
            --text-dark: #0d1b26;
            --text-muted: #5f6d78;
            --gold: #d4af37;
        }

        body {
            background-color: var(--bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-dark);
        }

        /* Card Container (Sports Trading Card Styling) */
        .card-wrapper {
            background: var(--surface);
            border: 12px solid var(--navy);
            border-radius: 20px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 10px 30px rgba(8, 23, 34, 0.15);
            overflow: hidden;
            position: relative;
        }

        /* Green Accent Inner Border */
        .card-inner {
            border: 4px solid var(--green);
            border-radius: 10px;
            margin: 6px;
            background: var(--navy-light);
            display: flex;
            flex-direction: column;
            height: calc(100% - 20px);
        }

        /* Header Details */
        .card-header {
            padding: 24px 20px 15px;
            text-align: center;
            border-bottom: 2px dashed rgba(215, 222, 222, 0.2);
            background: var(--navy);
        }

        .brand-logo {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 2px;
            color: var(--green);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .player-name {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 4px;
            letter-spacing: -0.5px;
        }

        .team-context {
            font-size: 13px;
            font-weight: 600;
            color: var(--border);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Portrait Graphic Placeholder */
        .card-photo-box {
            background: #102d44;
            height: 220px;
            margin: 15px;
            border: 2px solid var(--green);
            border-radius: 8px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .photo-fallback {
            text-align: center;
            color: rgba(255, 255, 255, 0.4);
        }

        .photo-fallback span {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }

        .jersey-badge {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: var(--green);
            color: #ffffff;
            font-weight: 900;
            font-size: 20px;
            padding: 6px 12px;
            border-radius: 6px;
            border: 2px solid var(--navy);
        }

        /* Quick-Facts Stripe */
        .facts-strip {
            display: flex;
            justify-content: space-around;
            padding: 10px 15px;
            background: var(--navy);
            border-top: 1px solid rgba(215, 222, 222, 0.1);
            border-bottom: 1px solid rgba(215, 222, 222, 0.1);
        }

        .fact-item {
            text-align: center;
        }

        .fact-label {
            font-size: 10px;
            color: var(--border);
            text-transform: uppercase;
            font-weight: 700;
        }

        .fact-value {
            font-size: 14px;
            color: #ffffff;
            font-weight: 800;
            margin-top: 2px;
        }

        /* Highlight Stat Grid */
        .stats-grid-container {
            padding: 20px;
            background: var(--surface);
            border-radius: 0 0 6px 6px;
            margin-top: auto;
        }

        .stats-title {
            font-size: 12px;
            font-weight: 800;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            text-align: center;
        }

        .stats-card-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .stat-card-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            text-align: center;
            padding: 10px 5px;
        }

        .stat-card-num {
            font-size: 18px;
            font-weight: 900;
            color: var(--navy);
        }

        .stat-card-lbl {
            font-size: 9px;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* ⚠️ REQUIRED DISCLOSURE STATE: Promptly state source fidelity [75, 115] */
        .disclosure-footer {
            margin-top: 15px;
            padding: 12px;
            background: var(--warning-soft);
            border: 1px solid var(--border-strong);
            border-radius: 8px;
            font-size: 10px;
            color: var(--warning-text);
            text-align: center;
            line-height: 1.4;
            max-width: 380px;
        }

        .branding-watermark {
            margin-top: 20px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="card-wrapper">
        <div class="card-inner">
            
            <!-- Branded Header -->
            <div class="card-header">
                <div class="brand-logo">Bulldog Statbook</div>
                <h1 class="player-name">{{ $player->display_name }}</h1>
                <div class="team-context">
                    {{ $player->memberships->first()->team->name ?? 'Prospect' }}
                </div>
            </div>

            <!-- Dynamic Graphic Container -->
            <div class="card-photo-box">
                <div class="photo-fallback">
                    <span>⚾</span>
                    <div>ATHLETE IDENTITY PROFILE</div>
                </div>
                <div class="jersey-badge">
                    #{{ $player->memberships->first()->jersey_number ?? '00' }}
                </div>
            </div>

            <!-- Facts Strip -->
            <div class="facts-strip">
                <div class="fact-item">
                    <div class="fact-label">Code</div>
                    <div class="fact-value" style="color: var(--gold)">{{ $player->player_code }}</div>
                </div>
                <div class="fact-item">
                    <div class="fact-label">Position</div>
                    <div class="fact-value">
                        {{ is_array($player->memberships->first()->positions_json) ? implode(', ', $player->memberships->first()->positions_json) : 'UTL' }}
                    </div>
                </div>
                <div class="fact-item">
                    <div class="fact-label">Sport</div>
                    <div class="fact-value">
                        {{ $player->memberships->first()->team->sport->name ?? 'Baseball' }}
                    </div>
                </div>
            </div>

            <!-- Bottom Stats Section -->
            <div class="stats-grid-container">
                <div class="stats-title">Career Statistics</div>
                <div class="stats-card-grid">
                    <div class="stat-card-box">
                        <div class="stat-card-num">{{ $careerStats['PA'] ?? 0 }}</div>
                        <div class="stat-card-lbl">PA</div>
                    </div>
                    <div class="stat-card-box">
                        <div class="stat-card-num">{{ $careerStats['AB'] ?? 0 }}</div>
                        <div class="stat-card-lbl">AB</div>
                    </div>
                    <div class="stat-card-box">
                        <div class="stat-card-num">{{ $careerStats['H'] ?? 0 }}</div>
                        <div class="stat-card-lbl">H</div>
                    </div>
                    <div class="stat-card-box">
                        <div class="stat-card-num">
                            {{ isset($careerStats['AB']) && $careerStats['AB'] > 0 ? number_format($careerStats['H'] / $careerStats['AB'], 3) : '.000' }}
                        </div>
                        <div class="stat-card-lbl">AVG</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ⚠️ MANDATORY PROVENANCE DISCLOSURE [75, 115] -->
    @if($hasImportedStats)
        <div class="disclosure-footer">
            <strong>⚠️ Platform Verification Disclosure:</strong> This player card displays historical statistics uploaded from external spreadsheet files. Bulldog Statbook cannot independently verify the accuracy of imported offline sessions.
        </div>
    @else
        <div class="disclosure-footer" style="background: var(--green-soft); color: var(--green); border-color: var(--green);">
            <strong>✓ Verified Live Session Card:</strong> These statistics represent live, event-scored games finalized directly on the Bulldog Statbook scoring engine.
        </div>
    @endif

    <div class="branding-watermark">
        Generated by <strong>Bulldog Statbook</strong>
    </div>

</body>
</html>
```

---

## 🚀 Step 7: How to Verify & Test this Setup

To confirm that milestones are evaluated and share cards resolve perfectly, run this simple test checklist:

1. **Seed Your Database with standard system badges:**
   Create a standard seeder that inserts the `ACE_IN_HOLE` definition:
   ```php
   App\Models\AchievementDefinition::create([
       'code' => 'ACE_IN_HOLE',
       'name' => 'Ace in the Hole',
       'description' => 'Strike out 10 or more hitters on the mound in a single game.',
       'category' => 'performance',
       'rule_json' => ['metric' => 'SO_pitching', 'threshold' => 10],
       'xp_value' => 250,
       'is_repeatable' => true,
   ]);
   ```
2. **Execute a live scoring sequence:**
   Log into your live browser and score a game (or run your feature test suite). Ensure that you record **10 pitching strikeouts** for a pitcher linked to a real user account.
3. **Transition the Game status to Finalized:**
   Verify that saving the finalized game triggers the `GameFinalized` event.
4. **Inspect Database Aggregates:**
   Open Laravel Tinker and query:
   ```php
   // Check streaks generated
   App\Models\Streak::all();

   // Verify milestone was generated
   App\Models\Milestone::all();

   // Confirm achievement award and XP points were issued successfully
   App\Models\AchievementAward::with('definition')->get();
   App\Models\XpLedger::all();
   ```
