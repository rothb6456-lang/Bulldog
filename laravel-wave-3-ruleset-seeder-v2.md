# Wave 3: Ruleset Seeder v2 (Extended Sports Rulesets)

This revised guide expands your `RulesetSeeder` to support a much broader variety of leagues across baseball and softball, including youth, scholastic, and adult configurations. 

These rulesets leverage the `config_json` schema column [137, 260] to cleanly encapsulate division-specific regulations like run rules, home-run caps, continuous batting orders, and count modifications without altering your structured database tables.

---

## 🛠️ Step 1: Replace/Create the Seeder File

In your GitHub Codespace, create or replace the file at **`database/seeders/RulesetSeeder.php`** with this fully expanded seeder class:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RulesetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch sports seeded from SportSeeder to establish proper foreign keys
        $baseball = DB::table('sports')->where('code', 'baseball')->first();
        $softball = DB::table('sports')->where('code', 'softball')->first();

        if (!$baseball || !$softball) {
            $this->command->error('Sports must be seeded first. Please run SportSeeder.');
            return;
        }

        DB::table('rulesets')->insert([
            // 1. Little League Baseball Ruleset
            [
                'id' => Str::uuid()->toString(),
                'sport_id' => $baseball->id,
                'name' => 'Little League Baseball (Major Division)',
                'config_json' => json_encode([
                    'innings_per_game' => 6,
                    'continuous_batting_order' => true, // CBO is standard in LL
                    'designated_hitter_allowed' => false,
                    'max_runs_per_inning' => null, // No limit in Majors
                    'run_rules' => [
                        ['innings' => 3, 'runs_ahead' => 15],
                        ['innings' => 4, 'runs_ahead' => 10],
                        ['innings' => 5, 'runs_ahead' => 8],
                    ],
                    'courtesy_runner_rule' => 'catcher_and_pitcher_only', // Allowed with 2 outs to speed up play
                    'pitch_count_limits' => [
                        'age_11_12' => 85,
                        'age_9_10' => 75,
                        'age_7_8' => 50,
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 2. NFHS High School Baseball Ruleset
            [
                'id' => Str::uuid()->toString(),
                'sport_id' => $baseball->id,
                'name' => 'NFHS High School Baseball',
                'config_json' => json_encode([
                    'innings_per_game' => 7,
                    'continuous_batting_order' => false, // Uses standard 9-player lineup with substitutions
                    'designated_hitter_allowed' => true, // DH rule is standard in NFHS scholastic play
                    'max_runs_per_inning' => null,
                    'run_rules' => [
                        ['innings' => 4, 'runs_ahead' => 15],
                        ['innings' => 5, 'runs_ahead' => 10],
                    ],
                    'courtesy_runner_rule' => 'pitcher_and_catcher_anytime', // NFHS specific courtesy runner
                    'tie_breaker_rule' => 'standard', // Normal extra innings rules
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 3. NFHS High School Fastpitch Softball Ruleset
            [
                'id' => Str::uuid()->toString(),
                'sport_id' => $softball->id,
                'name' => 'NFHS High School Fastpitch Softball',
                'config_json' => json_encode([
                    'innings_per_game' => 7,
                    'continuous_batting_order' => false, // Uses standard 9-player lineup with substitutions
                    'designated_player_flex_allowed' => true, // DP/Flex rule is standard in NFHS Fastpitch
                    'max_runs_per_inning' => null,
                    'run_rules' => [
                        ['innings' => 3, 'runs_ahead' => 15],
                        ['innings' => 5, 'runs_ahead' => 10],
                    ],
                    'courtesy_runner_rule' => 'pitcher_and_catcher_anytime', // For pitcher/catcher of record
                    'tie_breaker_rule' => 'international_tie_breaker', // Start with runner on second in extra innings
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 4. USSSA Adult Slowpitch Softball Ruleset
            [
                'id' => Str::uuid()->toString(),
                'sport_id' => $softball->id,
                'name' => 'USSSA Adult Slowpitch Softball',
                'config_json' => json_encode([
                    'innings_per_game' => 7,
                    'continuous_batting_order' => false, // Standard slowpitch EH/lineup rules
                    'extra_hitters_allowed' => true, // Allows up to 12 batters
                    'max_runs_per_inning' => null,
                    'run_rules' => [
                        ['innings' => 3, 'runs_ahead' => 20],
                        ['innings' => 4, 'runs_ahead' => 15],
                        ['innings' => 5, 'runs_ahead' => 10],
                    ],
                    'home_run_limit' => 4, // Class D standard home-run limit per game
                    'count_start' => '1_1_with_no_foul', // Starts with 1 Ball, 1 Strike count; no courtesy foul allowed
                    'courtesy_runner_rule' => 'one_per_inning_any_player', // Any single player on roster once per inning
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // 5. USA Softball (ASA) Adult Slowpitch Ruleset
            [
                'id' => Str::uuid()->toString(),
                'sport_id' => $softball->id,
                'name' => 'USA Softball (ASA) Adult Slowpitch',
                'config_json' => json_encode([
                    'innings_per_game' => 7,
                    'continuous_batting_order' => false,
                    'extra_hitters_allowed' => true,
                    'max_runs_per_inning' => null,
                    'run_rules' => [
                        ['innings' => 3, 'runs_ahead' => 20],
                        ['innings' => 4, 'runs_ahead' => 15],
                        ['innings' => 5, 'runs_ahead' => 12],
                    ],
                    'home_run_limit' => 2, // Class D standard home-run limit per game (USA Softball)
                    'count_start' => '1_1_with_courtesy_foul', // Starts 1-1; 1 courtesy foul allowed after 2 strikes
                    'courtesy_runner_rule' => 'one_per_inning_last_out', // Allowed for player making the last official out
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
```

---

## 🛠️ Step 2: Ensure Seeder Registration

Ensure your main **`database/seeders/DatabaseSeeder.php`** class triggers both the sports and ruleset seeders:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SportSeeder::class,
            RulesetSeeder::class, // <-- Runs rulesets automatically
        ]);
    }
}
```

---

## 🚀 Step 3: Run and Verify the Updated Seeder

1. **Rebuild the database and run seeds:**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Open interactive verification console (Tinker):**
   ```bash
   php artisan tinker
   ```

3. **Validate the newly loaded rulesets:**
   ```php
   // Fetch and check NFHS High School Baseball
   $hsBase = App\Models\Ruleset::where('name', 'NFHS High School Baseball')->first();
   $hsBase->config_json['innings_per_game']; // Outputs: 7
   $hsBase->config_json['designated_hitter_allowed']; // Outputs: true

   // Fetch and check USA Slowpitch Softball
   $usaSoft = App\Models\Ruleset::where('name', 'like', '%USA Softball%')->first();
   $usaSoft->config_json['home_run_limit']; // Outputs: 2
   $usaSoft->config_json['count_start']; // Outputs: "1_1_with_courtesy_foul"
   ```
