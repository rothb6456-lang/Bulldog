# Wave 5: Career History, Aggregation, and Historical Imports

This document details the database migrations, Eloquent models, parsing validation, transaction-safe Action classes, web controllers, and route registrations to implement **Wave 5: Career History, Aggregation, and Historical Imports** for Bulldog Statbook.

In alignment with the product blueprints:
1. **Longitudinal Semantics:** A player's career spans multiple teams, seasons, and leagues [68, 78].
2. **Source Transparency:** Imported historical data is explicitly labeled by source type (e.g., `uploaded_import`, `manual_historical`) and fidelity level (e.g., `season_totals`, `box_score`) [75, 114].
3. **Fidelity Disclosures:** Displays a mandatory limitation banner: *"Bulldog values longitudinal history but cannot independently verify uploaded/non-official entries."* [75, 115].
4. **Projections vs. Truth:** Aggregate tables are cache tables entirely rebuildable on demand from live events and imported lines [71, 148].
5. **No False Progression:** Imported statistics contribute to career totals but are strictly excluded from earning XP [77, 115, 144].

---

## 🛠️ Step 1: Database Migrations

Create these migrations to house historical import records and cache combined aggregations. They use PostgreSQL-portable definitions and strict foreign key mappings [129, 206].

### Migration 1: `create_season_and_career_aggregates_tables`
Caches combined on-the-fly calculations for high-performance player profiles and leaderboards [141, 147].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_aggregates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // 'player', 'team', 'coach' [141]
            $table->uuid('subject_id')->index();
            $table->string('season_label')->index(); // e.g., 'Summer 2026' [152]
            
            $table->string('stat_key');   // e.g., 'AB', 'H', 'HR', 'RBI'
            $table->decimal('stat_value', 8, 2)->default(0.00);
            
            // Tracks whether any part of this season contains unverified imported data
            $table->string('source_type')->default('bulldog_derived'); // 'bulldog_derived', 'uploaded_import', 'manual_historical', 'mixed' [150]
            $table->string('fidelity_level')->default('event_complete'); // 'season_totals', 'box_score', 'game_log', 'event_complete' [150]
            
            $table->timestamps();
            
            $table->unique(['subject_type', 'subject_id', 'season_label', 'stat_key'], 'subject_season_stat_unique');
        });

        Schema::create('career_aggregates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject_type'); // 'player', 'team', 'coach' [141]
            $table->uuid('subject_id')->index();
            
            $table->string('stat_key');
            $table->decimal('stat_value', 10, 2)->default(0.00);
            
            $table->string('source_type')->default('bulldog_derived');
            $table->string('fidelity_level')->default('event_complete');
            
            $table->timestamps();
            
            $table->unique(['subject_type', 'subject_id', 'stat_key'], 'subject_career_stat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_aggregates');
        Schema::dropIfExists('career_aggregates');
    }
};
```

### Migration 2: `create_historical_imports_tables`
Captures spreadsheet uploads and maps unverified inputs with strong uploader attribution [83, 142].
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('import_type'); // 'season_stats', 'game_log', 'team_history' [269]
            $table->string('context_type'); // 'player', 'team', 'league' [142]
            $table->uuid('context_id')->index();
            
            $table->string('source_label'); // e.g., 'GameChanger CSV', 'Paper Scorebook Manual Entry' [142, 148]
            $table->string('fidelity_level')->default('season_totals'); // 'season_totals', 'box_score', 'game_log' [150]
            $table->string('verification_status')->default('pending'); // 'pending', 'approved', 'rejected'
            
            $table->uuid('uploaded_by_user_id')->nullable();
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('imported_stat_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('historical_import_id')->index();
            $table->foreign('historical_import_id')->references('id')->on('historical_imports')->onDelete('cascade');
            
            $table->string('subject_type')->default('player'); // 'player' or 'team'
            $table->uuid('subject_id')->index(); // player_identity_id or team_id
            
            $table->string('season_key'); // e.g., 'Spring 2025' [269]
            $table->date('game_date')->nullable(); // Null if season-total fidelity
            
            // Flexible schema to store the variable columns of uploaded tables safely [142, 152]
            $table->json('stat_blob_json'); 
            
            $table->string('source_row_identifier')->nullable(); // Track row indexes for auditing [269]
            $table->timestamps();
        });

        Schema::create('import_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('historical_import_id')->index();
            $table->uuid('user_id')->nullable();
            $table->string('action'); // 'uploaded', 'previewed', 'confirmed', 'voided'
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_audit_logs');
        Schema::dropIfExists('imported_stat_lines');
        Schema::dropIfExists('historical_imports');
    }
};
```

---

## 💻 Step 2: Eloquent Models

Create these models inside your **`app/Models/`** folder. They implement standard non-incrementing UUID settings and JSON cast structures [206, 207].

### 1. `app/Models/SeasonAggregate.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SeasonAggregate extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'season_label',
        'stat_key',
        'stat_value',
        'source_type',
        'fidelity_level',
    ];

    public function subject()
    {
        return $this->morphTo();
    }
}
```

### 2. `app/Models/CareerAggregate.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CareerAggregate extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'stat_key',
        'stat_value',
        'source_type',
        'fidelity_level',
    ];

    public function subject()
    {
        return $this->morphTo();
    }
}
```

### 3. `app/Models/HistoricalImport.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricalImport extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'import_type',
        'context_type',
        'context_id',
        'source_label',
        'fidelity_level',
        'verification_status',
        'uploaded_by_user_id',
        'notes',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function statLines(): HasMany
    {
        return $this->hasMany(ImportedStatLine::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ImportAuditLog::class);
    }
}
```

### 4. `app/Models/ImportedStatLine.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportedStatLine extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'historical_import_id',
        'subject_type',
        'subject_id',
        'season_key',
        'game_date',
        'stat_blob_json',
        'source_row_identifier',
    ];

    protected $casts = [
        'stat_blob_json' => 'array',
        'game_date' => 'date',
    ];

    public function historicalImport(): BelongsTo
    {
        return $this->belongsTo(HistoricalImport::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
```

### 5. `app/Models/ImportAuditLog.php`
```php
<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ImportAuditLog extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'historical_import_id',
        'user_id',
        'action',
        'metadata_json',
    ];

    protected $casts = [
        'metadata_json' => 'array',
    ];
}
```

---

## 🔒 Step 3: Parse CSV History Action

This service layer parses uploaded files, handles header verification, and structures statistical blobs. Create this action class under **`app/Actions/Imports/ParseCsvImportAction.php`** [201, 232].

```php
<?php

namespace App\Actions\Imports;

use App\Models\HistoricalImport;
use App\Models\ImportedStatLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParseCsvImportAction
{
    /**
     * Parse the CSV file stream and store unconfirmed imported rows transaction-safely [114].
     */
    public function execute(HistoricalImport $import, string $csvFilePath, array $mappings): int
    {
        return DB::transaction(function () use ($import, $csvFilePath, $mappings) {
            $handle = fopen($csvFilePath, 'r');
            if (!$handle) {
                throw new \InvalidArgumentException("Unable to open CSV file.");
            }

            // 1. Fetch CSV headers to match mappings
            $headers = fgetcsv($handle, 1000, ",");
            $headers = array_map('trim', $headers);

            $rowCount = 0;

            // 2. Map CSV column indexes
            $playerIdentityColIndex = $this->resolveMappedIndex($headers, $mappings['player_identity_id'] ?? null);
            $seasonColIndex = $this->resolveMappedIndex($headers, $mappings['season_key'] ?? null);
            $gameDateColIndex = $this->resolveMappedIndex($headers, $mappings['game_date'] ?? null);

            // Establish mapping indices for common performance metrics
            $statMappingIndexes = [];
            foreach ($mappings['stats'] ?? [] as $statKey => $csvHeaderName) {
                $idx = $this->resolveMappedIndex($headers, $csvHeaderName);
                if ($idx !== null) {
                    $statMappingIndexes[$statKey] = $idx;
                }
            }

            // 3. Process each CSV row
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $rowCount++;
                
                // Resolve linked PlayerIdentity ID (usually selected in preview/confirm screen or resolved by code/name)
                $resolvedPlayerId = $playerIdentityColIndex !== null ? trim($row[$playerIdentityColIndex]) : null;
                if (!$resolvedPlayerId) {
                    continue; // Roster lines must map cleanly to a PlayerIdentity [136]
                }

                $seasonKey = $seasonColIndex !== null ? trim($row[$seasonColIndex]) : 'Historical Unassigned';
                $gameDate = ($gameDateColIndex !== null && !empty($row[$gameDateColIndex])) ? date('Y-m-d', strtotime(trim($row[$gameDateColIndex]))) : null;

                // Build structured metrics JSON blob
                $statsBlob = [];
                foreach ($statMappingIndexes as $statKey => $colIndex) {
                    $statsBlob[$statKey] = (float) trim($row[$colIndex]);
                }

                // Append unverified line item
                ImportedStatLine::create([
                    'id' => Str::uuid()->toString(),
                    'historical_import_id' => $import->id,
                    'subject_type' => 'player',
                    'subject_id' => $resolvedPlayerId,
                    'season_key' => $seasonKey,
                    'game_date' => $gameDate,
                    'stat_blob_json' => $statsBlob,
                    'source_row_identifier' => "CSV Row #{$rowCount}",
                ]);
            }

            fclose($handle);

            // Audit the parse completion
            $import->auditLogs()->create([
                'user_id' => $import->uploaded_by_user_id,
                'action' => 'previewed',
                'metadata_json' => [
                    'records_parsed' => $rowCount,
                    'mappings_used' => $mappings,
                ]
            ]);

            return $rowCount;
        });
    }

    protected function resolveMappedIndex(array $headers, ?string $headerName): ?int
    {
        if (!$headerName) {
            return null;
        }
        $index = array_search($headerName, $headers);
        return $index !== false ? (int)$index : null;
    }
}
```

---

## 📈 Step 4: Dynamic History & Aggregation Engine

This is the system-wide aggregation engine. When triggered, it queries **both** authoritative live-scored game events (`game_player_stats`) and unverified uploads (`imported_stat_lines`), compiles combined metrics, and flattens them into highly indexable cache tables [71, 148, 274].

Create this under **`app/Actions/History/RecompilePlayerHistoryAction.php`** [201]:

```php
<?php

namespace App\Actions\History;

use App\Models\PlayerIdentity;
use App\Models\GamePlayerStat;
use App\Models\ImportedStatLine;
use App\Models\SeasonAggregate;
use App\Models\CareerAggregate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecompilePlayerHistoryAction
{
    /**
     * Completely recompile high-performance stats for a specific athlete.
     * Guaranteed to be completely auditable and rebuildable on demand [148, 274].
     */
    public function execute(string $playerIdentityId): void
    {
        DB::transaction(function () use ($playerIdentityId) {
            // 1. Clear current cached aggregates to prevent calculation drift [153]
            SeasonAggregate::where('subject_type', 'player')->where('subject_id', $playerIdentityId)->delete();
            CareerAggregate::where('subject_type', 'player')->where('subject_id', $playerIdentityId)->delete();

            $compiledSeasons = [];
            $compiledCareer = [];

            // 2. Fetch authoritative live game statistics (e.g., from Live scoring engine) [141]
            $liveStats = GamePlayerStat::where('player_identity_id', $playerIdentityId)
                ->with(['game'])
                ->get();

            foreach ($liveStats as $stat) {
                // Determine season context dynamically (using season_label from associated Team context or fallback) [65]
                $season = $stat->game->season_label ?? 'Spring 2026';
                $key = $stat->stat_key;
                $val = (float)$stat->stat_value;

                $this->accumulate($compiledSeasons, $season, $key, $val, 'bulldog_derived', 'event_complete');
                $this->accumulate($compiledCareer, 'career', $key, $val, 'bulldog_derived', 'event_complete');
            }

            // 3. Fetch unverified approved historical imports [75, 114]
            $importedLines = ImportedStatLine::where('subject_type', 'player')
                ->where('subject_id', $playerIdentityId)
                ->whereHas('historicalImport', function ($query) {
                    $query->where('verification_status', 'approved'); // Only approved imports enter career totals [83]
                })
                ->with(['historicalImport'])
                ->get();

            foreach ($importedLines as $line) {
                $season = $line->season_key;
                $import = $line->historicalImport;
                
                $sourceType = $import->import_type === 'verified_import' ? 'verified_import' : 'uploaded_import';
                $fidelity = $import->fidelity_level;

                foreach ($line->stat_blob_json as $key => $val) {
                    $this->accumulate($compiledSeasons, $season, $key, (float)$val, $sourceType, $fidelity);
                    $this->accumulate($compiledCareer, 'career', $key, (float)$val, $sourceType, $fidelity);
                }
            }

            // 4. Flush Season aggregates to high-performance tables
            foreach ($compiledSeasons as $seasonLabel => $metrics) {
                foreach ($metrics as $statKey => $meta) {
                    SeasonAggregate::create([
                        'id' => Str::uuid()->toString(),
                        'subject_type' => 'player',
                        'subject_id' => $playerIdentityId,
                        'season_label' => $seasonLabel,
                        'stat_key' => $statKey,
                        'stat_value' => $meta['value'],
                        'source_type' => $meta['source_type'],
                        'fidelity_level' => $meta['fidelity'],
                    ]);
                }
            }

            // 5. Flush Career aggregates to high-performance tables
            foreach ($compiledCareer['career'] ?? [] as $statKey => $meta) {
                CareerAggregate::create([
                    'id' => Str::uuid()->toString(),
                    'subject_type' => 'player',
                    'subject_id' => $playerIdentityId,
                    'stat_key' => $statKey,
                    'stat_value' => $meta['value'],
                    'source_type' => $meta['source_type'],
                    'fidelity_level' => $meta['fidelity'],
                ]);
            }
        });
    }

    protected function accumulate(array &$compiled, string $scopeKey, string $statKey, float $value, string $sourceType, string $fidelity): void
    {
        if (!isset($compiled[$scopeKey])) {
            $compiled[$scopeKey] = [];
        }

        if (!isset($compiled[$scopeKey][$statKey])) {
            $compiled[$scopeKey][$statKey] = [
                'value' => 0.0,
                'source_type' => $sourceType,
                'fidelity' => $fidelity,
            ];
        } else {
            $compiled[$scopeKey][$statKey]['value'] += $value;
            
            // Source mixing logic: If data is mixed between live scoring and imported stats, label it mixed [150]
            if ($compiled[$scopeKey][$statKey]['source_type'] !== $sourceType) {
                $compiled[$scopeKey][$statKey]['source_type'] = 'mixed';
            }

            // Fidelity downgrade logic: The overall aggregate takes on the lowest common fidelity denominator
            if ($fidelity === 'season_totals' || $compiled[$scopeKey][$statKey]['fidelity'] === 'season_totals') {
                $compiled[$scopeKey][$statKey]['fidelity'] = 'season_totals';
            } else if ($fidelity === 'box_score' || $compiled[$scopeKey][$statKey]['fidelity'] === 'box_score') {
                $compiled[$scopeKey][$statKey]['fidelity'] = 'box_score';
            }
        }
    }
}
```

---

## 🔌 Step 5: Web & API Routing

Append these routes to map your CSV uploads and athlete profiles cleanly [207, 208].

### Web Interface Routing (`routes/web.php`)
```php
<?php

use App\Http\Controllers\Web\PlayerCareerController;
use App\Http\Controllers\Web\ImportWizardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Player Career Page [161]
    Route::get('/players/{player}/career', [PlayerCareerController::class, 'show'])->name('players.career');

    // Historical Import UI Wizard [115]
    Route::get('/imports/create', [ImportWizardController::class, 'create'])->name('imports.create');
    Route::post('/imports', [ImportWizardController::class, 'store'])->name('imports.store');
    Route::get('/imports/{import}/mapping', [ImportWizardController::class, 'mapping'])->name('imports.mapping');
    Route::post('/imports/{import}/confirm', [ImportWizardController::class, 'confirm'])->name('imports.confirm');
});
```

### API Routing (`routes/api.php`)
```php
<?php

use App\Http\Controllers\Api\Imports\HistoricalImportApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // API-Driven Upload Pipelines [183, 184]
    Route::post('/imports', [HistoricalImportApiController::class, 'store']);
    Route::post('/imports/{import}/confirm', [HistoricalImportApiController::class, 'confirm']);
    Route::get('/imports/{import}', [HistoricalImportApiController::class, 'show']);
});
```

---

## 🎮 Step 6: Web Controllers & Requests

Create your Request validators and controller actions to coordinate the background mapping and profile compilation [201].

### 1. Request: `app/Http/Requests/Imports/StoreImportRequest.php`
```php
<?php

namespace App\Http\Requests\Imports;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Require uploader context-permissions [83, 115]
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'import_type' => ['required', 'string', 'in:season_stats,game_log,team_history'],
            'context_type' => ['required', 'string', 'in:player,team'],
            'context_id' => ['required', 'uuid'],
            'source_label' => ['required', 'string', 'max:100'],
            'fidelity_level' => ['required', 'string', 'in:season_totals,box_score,game_log'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // Max 5MB [184]
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
```

### 2. Web Controller: `app/Http/Controllers/Web/PlayerCareerController.php`
Loads consolidated career summaries, and passes data to your high-density athlete summary template [78, 161].
```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PlayerIdentity;
use App\Models\SeasonAggregate;
use App\Models\CareerAggregate;
use Illuminate\Http\Request;

class PlayerCareerController extends Controller
{
    public function show(Request $request, PlayerIdentity $player)
    {
        // Enforce contextual view policies [80, 271]
        $this->authorize('view', $player);

        // 1. Fetch overall career summaries
        $careerStats = CareerAggregate::where('subject_type', 'player')
            ->where('subject_id', $player->id)
            ->get()
            ->keyBy('stat_key');

        // 2. Fetch season-by-season aggregates
        $seasonStats = SeasonAggregate::where('subject_type', 'player')
            ->where('subject_id', $player->id)
            ->get()
            ->groupBy('season_label');

        // 3. Render the visual workspace
        return view('profile.career', compact('player', 'careerStats', 'seasonStats'));
    }
}
```

### 3. Web Wizard Controller: `app/Http/Controllers/Web/ImportWizardController.php`
Manages the visual multipage workflow to preview, map, and run dynamic imports [115, 184].
```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\StoreImportRequest;
use App\Models\HistoricalImport;
use App\Actions\Imports\ParseCsvImportAction;
use App\Actions\History\RecompilePlayerHistoryAction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImportWizardController extends Controller
{
    protected ParseCsvImportAction $parser;
    protected RecompilePlayerHistoryAction $recompiler;

    public function __construct(ParseCsvImportAction $parser, RecompilePlayerHistoryAction $recompiler)
    {
        $this->parser = $parser;
        $this->recompiler = $recompiler;
    }

    public function create()
    {
        return view('imports.create');
    }

    public function store(StoreImportRequest $request)
    {
        $file = $request->file('csv_file');
        
        // Save uploaded CSV temporarily
        $tempPath = $file->storeAs('temp-imports', Str::uuid()->toString() . '.csv');

        // Create the shell record
        $import = HistoricalImport::create([
            'id' => Str::uuid()->toString(),
            'import_type' => $request->import_type,
            'context_type' => $request->context_type,
            'context_id' => $request->context_id,
            'source_label' => $request->source_label,
            'fidelity_level' => $request->fidelity_level,
            'verification_status' => 'pending',
            'uploaded_by_user_id' => $request->user()->id,
            'notes' => $request->notes,
        ]);

        $import->auditLogs()->create([
            'user_id' => $request->user()->id,
            'action' => 'uploaded',
            'metadata_json' => ['file_path' => $tempPath],
        ]);

        // Put filepath into session for mapping preview
        session(['import_csv_path' => $tempPath]);

        return redirect()->route('imports.mapping', $import);
    }

    public function mapping(HistoricalImport $import)
    {
        $csvPath = session('import_csv_path');
        if (!$csvPath || !file_exists(storage_path('app/' . $csvPath))) {
            return redirect()->route('imports.create')->withErrors(['csv_file' => 'Upload session expired.']);
        }

        // Read the CSV headers to let the coach perform visual column mapping
        $handle = fopen(storage_path('app/' . $csvPath), 'r');
        $headers = fgetcsv($handle, 1000, ",");
        fclose($handle);

        return view('imports.mapping', compact('import', 'headers'));
    }

    public function confirm(Request $request, HistoricalImport $import)
    {
        $csvPath = session('import_csv_path');
        
        $mappings = $request->validate([
            'player_identity_id' => ['required', 'string'],
            'season_key' => ['required', 'string'],
            'stats' => ['required', 'array'],
        ]);

        // Parse and write imported lines
        $rowCount = $this->parser->execute($import, storage_path('app/' . $csvPath), $mappings);

        // Transactionally approve and sync to aggregations [83]
        $import->update(['verification_status' => 'approved']);
        
        $import->auditLogs()->create([
            'user_id' => $request->user()->id,
            'action' => 'confirmed',
            'metadata_json' => ['rows_imported' => $rowCount],
        ]);

        // Trigger dynamic compilation for this athlete
        $playerIdentityId = $import->context_id; // In player-context import
        $this->recompiler->execute($playerIdentityId);

        // Clear session file path
        session()->forget('import_csv_path');

        return redirect()->route('players.career', $playerIdentityId)->with('success', "Import completed! {$rowCount} lines processed and merged into career history.");
    }
}
```
