# Wave 4: Live Event Scoring & Real-time Stat Projections

This document details the database migrations, Eloquent models, validation schemas, Action classes, and API controller endpoints to implement **Wave 4: Live Event Scoring (Pitches, Hits, Walks) and Real-time Stat Aggregations** for Bulldog Statbook.

In alignment with the core product thesis, **raw events are the authoritative source of truth, and derived statistics are entirely recalculable from the event stream** [59, 71, 72, 148]. This approach ensures data auditability, absolute accuracy, and the ability to dynamically recalculate career and season aggregates whenever a game-scoring entry is corrected [73, 112].

---

## 🛠️ Step 1: Database Migrations

Create these five migrations in your `database/migrations/` folder. They employ standard PostgreSQL-portable types and explicit indices to support sub-second query performance on event streams [129, 146].

### Migration 1: `create_game_events_table`
Handles the canonical sequence-ordered stream of actions occurring on the field [139, 262].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            
            $table->integer('sequence_number')->index();
            $table->string('event_family'); // e.g., 'plate_appearance', 'baserunning', 'pitching', 'game_admin'
            $table->string('event_type');   // e.g., 'pitch', 'single', 'walk', 'strikeout', 'stolen_base'
            
            // Contextual game-state snapshot prior to event
            $table->integer('inning_number');
            $table->string('half_inning'); // 'top', 'bottom'
            $table->integer('outs_before');
            $table->integer('outs_after');
            $table->integer('balls_before');
            $table->integer('strikes_before');
            $table->integer('balls_after');
            $table->integer('strikes_after');
            
            // Base states represented as string binary flags (e.g., '000' = empty, '101' = 1st & 3rd)
            $table->string('base_state_before')->default('000');
            $table->string('base_state_after')->default('000');
            
            $table->integer('score_home_before')->default(0);
            $table->integer('score_home_after')->default(0);
            $table->integer('score_away_before')->default(0);
            $table->integer('score_away_after')->default(0);
            
            // Flexible metadata for specialized event attributes (e.g., pitch speeds, spray vectors)
            $table->json('payload_json')->nullable(); 
            
            $table->uuid('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->boolean('is_voided')->default(false);
            $table->timestamps();
            
            $table->unique(['game_id', 'sequence_number'], 'game_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_events');
    }
};
```

### Migration 2: `create_game_event_players_table`
Maps event attributions cleanly to player identities, preventing massive column bloat in your main events table [139, 140].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_event_players', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_event_id')->index();
            $table->foreign('game_event_id')->references('id')->on('game_events')->onDelete('cascade');
            
            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('cascade');
            
            $table->string('role'); // 'batter', 'pitcher', 'runner', 'fielder', 'assisting_fielder'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_event_players');
    }
};
```

### Migration 3: `create_game_state_snapshots_table`
Caches full game checkpoints periodically (or on every significant play) to speed up loading and support robust scorebook reconstruction [263, 264].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_state_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            
            $table->uuid('game_event_id')->nullable()->index(); // Null for pre-game initialized state
            $table->foreign('game_event_id')->references('id')->on('game_events')->onDelete('cascade');
            
            $table->integer('sequence_number')->default(0);
            $table->integer('inning_number')->default(1);
            $table->string('half_inning')->default('top');
            $table->integer('outs')->default(0);
            $table->integer('balls')->default(0);
            $table->integer('strikes')->default(0);
            $table->integer('score_home')->default(0);
            $table->integer('score_away')->default(0);
            $table->string('base_state')->default('000'); // '100', '110', '111', etc.
            
            $table->uuid('current_batter_id')->nullable();
            $table->uuid('current_pitcher_id')->nullable();
            
            // JSON payloads to persist lineup pointers (batting order indexing) and live field charts
            $table->json('lineup_pointers')->nullable(); 
            $table->json('defensive_alignment')->nullable(); 
            
            $table->timestamps();
            
            $table->unique(['game_id', 'sequence_number'], 'game_snapshot_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_state_snapshots');
    }
};
```

### Migration 4 & 5: `create_game_stats_tables` (Player & Team Cache)
Provides real-time indexable aggregations of standard stats derived cleanly from the event stream [141, 265].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_player_stats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            
            $table->uuid('player_identity_id')->index();
            $table->foreign('player_identity_id')->references('id')->on('player_identities')->onDelete('cascade');
            
            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            
            $table->string('stat_key'); // 'AB', 'H', 'R', 'RBI', 'BB', 'SO', 'IP_outs', 'ER', etc.
            $table->decimal('stat_value', 8, 2)->default(0.00);
            $table->timestamps();
            
            $table->unique(['game_id', 'player_identity_id', 'stat_key'], 'player_game_stat_unique');
        });

        Schema::create('game_team_stats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id')->index();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            
            $table->uuid('team_id')->index();
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            
            $table->string('stat_key'); // 'AB', 'H', 'R', 'RBI', 'BB', 'SO', etc.
            $table->decimal('stat_value', 8, 2)->default(0.00);
            $table->timestamps();
            
            $table->unique(['game_id', 'team_id', 'stat_key'], 'team_game_stat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_player_stats');
        Schema::dropIfExists('game_team_stats');
    }
};
```

---

## 💻 Step 2: Eloquent Models

Create these files in your `app/Models/` folder, equipping each with your non-incrementing UUID settings:

### 1. `app/Models/GameEvent.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameEvent extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_id',
        'sequence_number',
        'event_family',
        'event_type',
        'inning_number',
        'half_inning',
        'outs_before',
        'outs_after',
        'balls_before',
        'strikes_before',
        'balls_after',
        'strikes_after',
        'base_state_before',
        'base_state_after',
        'score_home_before',
        'score_home_after',
        'score_away_before',
        'score_away_after',
        'payload_json',
        'created_by_user_id',
        'is_voided',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'is_voided' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function eventPlayers(): HasMany
    {
        return $this->hasMany(GameEventPlayer::class);
    }
}
```

### 2. `app/Models/GameEventPlayer.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameEventPlayer extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_event_id',
        'player_identity_id',
        'role',
    ];

    public function gameEvent(): BelongsTo
    {
        return $this->belongsTo(GameEvent::class);
    }

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class);
    }
}
```

### 3. `app/Models/GameStateSnapshot.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameStateSnapshot extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'game_id',
        'game_event_id',
        'sequence_number',
        'inning_number',
        'half_inning',
        'outs',
        'balls',
        'strikes',
        'score_home',
        'score_away',
        'base_state',
        'current_batter_id',
        'current_pitcher_id',
        'lineup_pointers',
        'defensive_alignment',
    ];

    protected $casts = [
        'lineup_pointers' => 'array',
        'defensive_alignment' => 'array',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function gameEvent(): BelongsTo
    {
        return $this->belongsTo(GameEvent::class);
    }
}
```

### 4. `app/Models/GamePlayerStat.php` & `app/Models/GameTeamStat.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayerStat extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'game_player_stats';

    protected $fillable = [
        'game_id',
        'player_identity_id',
        'team_id',
        'stat_key',
        'stat_value',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
```

*(Note: Replicate the structure of `GamePlayerStat` for `GameTeamStat`, swapping `player_identity_id` out and updating the `$table` property to `'game_team_stats'`)*

---

## ⚡ Step 3: Record Scoring Event Action

This Core Action is responsible for appending scored events safely, resolving contextual runners and outs, writing state snapshots, and dispatching real-time updates.

Create a new file at **`app/Actions/Scoring/RecordScoringEventAction.php`**:

```php
<?php

namespace App\Actions\Scoring;

use App\Models\Game;
use App\Models\GameEvent;
use App\Models\GameStateSnapshot;
use App\Actions\Scoring\RecalculateGameStatsAction;
use Illuminate\Support\Facades\DB;

class RecordScoringEventAction
{
    protected RecalculateGameStatsAction $recalculateStats;

    public function __construct(RecalculateGameStatsAction $recalculateStats)
    {
        $this->recalculateStats = $recalculateStats;
    }

    /**
     * Execute appending a live game scoring event.
     */
    public function execute(string $gameId, array $data, string $userId): GameEvent
    {
        return DB::transaction(function () use ($gameId, $data, $userId) {
            $game = Game::findOrFail($gameId);
            
            // 1. Fetch latest state snapshot to get baseline tracking
            $latestSnapshot = GameStateSnapshot::where('game_id', $gameId)
                ->orderBy('sequence_number', 'desc')
                ->first();

            $nextSequence = $latestSnapshot ? ($latestSnapshot->sequence_number + 1) : 1;
            
            $ballsBefore = $latestSnapshot ? $latestSnapshot->balls : 0;
            $strikesBefore = $latestSnapshot ? $latestSnapshot->strikes : 0;
            $outsBefore = $latestSnapshot ? $latestSnapshot->outs : 0;
            $baseStateBefore = $latestSnapshot ? $latestSnapshot->base_state : '000';
            
            $homeScoreBefore = $latestSnapshot ? $latestSnapshot->score_home : 0;
            $awayScoreBefore = $latestSnapshot ? $latestSnapshot->score_away : 0;
            
            $inning = $latestSnapshot ? $latestSnapshot->inning_number : 1;
            $halfInning = $latestSnapshot ? $latestSnapshot->half_inning : 'top';

            // 2. Resolve on-field events and update counts
            $ballsAfter = $ballsBefore;
            $strikesAfter = $strikesBefore;
            $outsAfter = $outsBefore;
            $baseStateAfter = $baseStateBefore;
            $homeScoreAfter = $homeScoreBefore;
            $awayScoreAfter = $awayScoreBefore;

            $type = $data['event_type']; // 'pitch', 'single', 'walk', 'strikeout'

            if ($type === 'pitch') {
                $pitchResult = $data['payload']['pitch_result'] ?? 'ball';
                if ($pitchResult === 'strike') {
                    $strikesAfter++;
                } else if ($pitchResult === 'ball') {
                    $ballsAfter++;
                }
            } else if ($type === 'single') {
                // Single: Empty count, place runner on 1st, advance others
                $ballsAfter = 0;
                $strikesAfter = 0;
                $baseStateAfter = $this->advanceRunners($baseStateBefore, 1, $homeScoreAfter, $awayScoreAfter, $halfInning);
            } else if ($type === 'walk') {
                $ballsAfter = 0;
                $strikesAfter = 0;
                $baseStateAfter = $this->advanceRunners($baseStateBefore, 1, $homeScoreAfter, $awayScoreAfter, $halfInning, true);
            } else if ($type === 'strikeout') {
                $ballsAfter = 0;
                $strikesAfter = 0;
                $outsAfter++;
            }

            // Inning transition check (3 outs)
            if ($outsAfter >= 3) {
                $outsAfter = 0;
                $ballsAfter = 0;
                $strikesAfter = 0;
                $baseStateAfter = '000';
                
                if ($halfInning === 'top') {
                    $halfInning = 'bottom';
                } else {
                    $halfInning = 'top';
                    $inning++;
                }
            }

            // 3. Create the authoritative Game Event record
            $event = GameEvent::create([
                'game_id' => $gameId,
                'sequence_number' => $nextSequence,
                'event_family' => $data['event_family'],
                'event_type' => $type,
                'inning_number' => $inning,
                'half_inning' => $halfInning,
                'outs_before' => $outsBefore,
                'outs_after' => $outsAfter,
                'balls_before' => $ballsBefore,
                'strikes_before' => $strikesBefore,
                'balls_after' => $ballsAfter,
                'strikes_after' => $strikesAfter,
                'base_state_before' => $baseStateBefore,
                'base_state_after' => $baseStateAfter,
                'score_home_before' => $homeScoreBefore,
                'score_home_after' => $homeScoreAfter,
                'score_away_before' => $awayScoreBefore,
                'score_away_after' => $awayScoreAfter,
                'payload_json' => $data['payload'] ?? null,
                'created_by_user_id' => $userId,
            ]);

            // 4. Map attributed players participating in this event
            if (!empty($data['players'])) {
                foreach ($data['players'] as $player) {
                    $event->eventPlayers()->create([
                        'player_identity_id' => $player['player_identity_id'],
                        'role' => $player['role'],
                    ]);
                }
            }

            // 5. Save the updated Game State Snapshot
            GameStateSnapshot::create([
                'game_id' => $gameId,
                'game_event_id' => $event->id,
                'sequence_number' => $nextSequence,
                'inning_number' => $inning,
                'half_inning' => $halfInning,
                'outs' => $outsAfter,
                'balls' => $ballsAfter,
                'strikes' => $strikesAfter,
                'score_home' => $homeScoreAfter,
                'score_away' => $awayScoreAfter,
                'base_state' => $baseStateAfter,
                'current_batter_id' => $data['current_batter_id'] ?? ($latestSnapshot->current_batter_id ?? null),
                'current_pitcher_id' => $data['current_pitcher_id'] ?? ($latestSnapshot->current_pitcher_id ?? null),
                'lineup_pointers' => $latestSnapshot ? $latestSnapshot->lineup_pointers : null,
                'defensive_alignment' => $latestSnapshot ? $latestSnapshot->defensive_alignment : null,
            ]);

            // 6. Trigger synchronous real-time statistical projection updates [275]
            $this->recalculateStats->execute($gameId);

            return $event;
        ]);
    }

    /**
     * Minimal helper to advance runners on a single or walk.
     */
    protected function advanceRunners(string $baseState, int $bases, int &$homeScore, int &$awayScore, string $halfInning, bool $isForced = false): string
    {
        $first = $baseState[0] === '1';
        $second = $baseState[1] === '1';
        $third = $baseState[2] === '1';

        if ($isForced) {
            // For walks (forced advancement rules)
            if ($first) {
                if ($second) {
                    if ($third) {
                        $this->awardRun($homeScore, $awayScore, $halfInning);
                    }
                    $third = true;
                }
                $second = true;
            }
            $first = true;
        } else {
            // For standard hit (advancing everyone by N bases)
            for ($i = 0; $i < $bases; $i++) {
                if ($third) {
                    $this->awardRun($homeScore, $awayScore, $halfInning);
                    $third = false;
                }
                if ($second) { $third = true; $second = false; }
                if ($first) { $second = true; $first = false; }
                if ($i === 0) { $first = true; }
            }
        }

        return ($first ? '1' : '0') . ($second ? '1' : '0') . ($third ? '1' : '0');
    }

    protected function awardRun(int &$homeScore, int &$awayScore, string $halfInning): void
    {
        if ($halfInning === 'top') {
            $awayScore++;
        } else {
            $homeScore++;
        }
    }
}
```

---

## 📊 Step 4: Real-time Recalculation Engine

The statistical engine reads the raw event logs, computes the box-score aggregates, and updates the index caches transactionally to guarantee data integrity [265, 274].

Create a new file at **`app/Actions/Scoring/RecalculateGameStatsAction.php`**:

```php
<?php

namespace App\Actions\Scoring;

use App\Models\GameEvent;
use App\Models\GamePlayerStat;
use App\Models\GameTeamStat;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class RecalculateGameStatsAction
{
    /**
     * Recalculates stats for a given game.
     */
    public function execute(string $gameId): void
    {
        DB::transaction(function () use ($gameId) {
            $game = Game::findOrFail($gameId);

            // 1. Clear existing statistics for this game session to ensure clean write
            GamePlayerStat::where('game_id', $gameId)->delete();
            GameTeamStat::where('game_id', $gameId)->delete();

            // 2. Load all active events chronologically
            $events = GameEvent::where('game_id', $gameId)
                ->where('is_voided', false)
                ->with('eventPlayers')
                ->orderBy('sequence_number', 'asc')
                ->get();

            $playerStats = [];
            $teamStats = [];

            // 3. Process events to compile metrics
            foreach ($events as $event) {
                $type = $event->event_type;
                
                // Fetch players attributed to the event
                $batter = $event->eventPlayers->firstWhere('role', 'batter');
                $pitcher = $event->eventPlayers->firstWhere('role', 'pitcher');

                if ($type === 'single') {
                    if ($batter) {
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'AB', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'H', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'PA', 1);
                    }
                    if ($pitcher) {
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'H_allowed', 1);
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'BF', 1);
                    }
                } else if ($type === 'walk') {
                    if ($batter) {
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'BB', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'PA', 1);
                    }
                    if ($pitcher) {
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'BB_allowed', 1);
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'BF', 1);
                    }
                } else if ($type === 'strikeout') {
                    if ($batter) {
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'AB', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'SO', 1);
                        $this->increment($playerStats, $gameId, $batter->player_identity_id, $game->home_team_id, 'PA', 1);
                    }
                    if ($pitcher) {
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'SO_pitching', 1);
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'IP_outs', 1);
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'BF', 1);
                    }
                } else if ($type === 'pitch') {
                    if ($pitcher) {
                        $pitchResult = $event->payload_json['pitch_result'] ?? 'ball';
                        if ($pitchResult === 'strike') {
                            $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'strikes_thrown', 1);
                        } else {
                            $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'balls_thrown', 1);
                        }
                        $this->increment($playerStats, $gameId, $pitcher->player_identity_id, $game->away_team_id, 'total_pitches', 1);
                    }
                }
            }

            // 4. Batch-insert calculated statistics into database tables
            foreach ($playerStats as $playerKey => $value) {
                [$playerId, $teamId, $statKey] = explode('::', $playerKey);
                GamePlayerStat::create([
                    'game_id' => $gameId,
                    'player_identity_id' => $playerId,
                    'team_id' => $teamId,
                    'stat_key' => $statKey,
                    'stat_value' => $value,
                ]);

                // Record parallel team totals [265]
                $this->increment($teamStats, $gameId, null, $teamId, $statKey, $value);
            }

            foreach ($teamStats as $teamKey => $value) {
                [,, $teamId, $statKey] = explode('::', 'null::null::' . $teamKey);
                GameTeamStat::create([
                    'game_id' => $gameId,
                    'team_id' => $teamId,
                    'stat_key' => $statKey,
                    'stat_value' => $value,
                ]);
            }
        });
    }

    private function increment(array &$statsArray, string $gameId, ?string $playerId, string $teamId, string $statKey, float $amount): void
    {
        $key = $playerId 
            ? "{$playerId}::{$teamId}::{$statKey}"
            : "{$teamId}::{$statKey}";

        if (!isset($statsArray[$key])) {
            $statsArray[$key] = 0;
        }
        $statsArray[$key] += $amount;
    }
}
```

---

## 📝 Step 5: Form Request Validation

Create this validator to enforce clean scoring payload entries.

Create a new file at **`app/Http/Requests/Scoring/StoreScoringEventRequest.php`**:

```php
<?php

namespace App\Http\Requests\Scoring;

use Illuminate\Foundation\Http\FormRequest;

class StoreScoringEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Explicitly check game scoring authority via policy layer
        return $this->user()->can('score', $this->route('game'));
    }

    public function rules(): array
    {
        return [
            'event_family' => ['required', 'string', 'in:plate_appearance,baserunning,pitching,defense,game_admin'],
            'event_type' => ['required', 'string', 'in:pitch,single,double,triple,home_run,walk,strikeout,stolen_base,substitution'],
            'current_batter_id' => ['nullable', 'uuid', 'exists:player_identities,id'],
            'current_pitcher_id' => ['nullable', 'uuid', 'exists:player_identities,id'],
            'payload' => ['nullable', 'array'],
            'players' => ['nullable', 'array'],
            'players.*.player_identity_id' => ['required', 'uuid', 'exists:player_identities,id'],
            'players.*.role' => ['required', 'string', 'in:batter,pitcher,runner,fielder'],
        ];
    }
}
```

---

## ⚡ Step 6: API Controller Endpoints

Create this controller under **`app/Http/Controllers/Api/Scoring/GameEventController.php`**:

```php
<?php

namespace App\Http\Controllers\Api\Scoring;

use App\Actions\Scoring\RecordScoringEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Scoring\StoreScoringEventRequest;
use App\Models\Game;
use App\Models\GameEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameEventController extends Controller
{
    protected RecordScoringEventAction $recordScoringEvent;

    public function __construct(RecordScoringEventAction $recordScoringEvent)
    {
        $this->recordScoringEvent = $recordScoringEvent;
    }

    /**
     * Record a new game play event.
     */
    public function store(StoreScoringEventRequest $request, Game $game): JsonResponse
    {
        $event = $this->recordScoringEvent->execute(
            $game->id,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Event logged successfully.',
            'event' => $event->load('eventPlayers'),
        ], 210);
    }

    /**
     * Retrieve chronological history of game events.
     */
    public function index(Request $request, Game $game): JsonResponse
    {
        $this->authorize('view', $game);

        $events = GameEvent::where('game_id', $game->id)
            ->where('is_voided', false)
            ->with('eventPlayers.playerIdentity')
            ->orderBy('sequence_number', 'asc')
            ->get();

        return response()->json([
            'events' => $events
        ]);
    }
}
```

---

## 🔌 Step 7: Routing API Definitions

Append these endpoints to your authenticated route group inside **`routes/api.php`**:

```php
<?php

use App\Http\Controllers\Api\Scoring\GameEventController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Game Scoring Event Stream
    Route::post('/games/{game}/events', [GameEventController::class, 'store']);
    Route::get('/games/{game}/events', [GameEventController::class, 'index']);
});
```
