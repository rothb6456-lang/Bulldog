# Bulldog Statbook — Free Baseball & Softball Statbook Platform

> **Status:** MVP Core Complete (Waves 1–7 Verified)  
> **Brand Ecosystem:** Bulldog Stats & Sports Innovation  
> **Target Subdomain:** `statbook.bulldogstats.com` (Separate hosted web app from main WordPress site)

---

## ⚾ About Bulldog Statbook

**Bulldog Statbook** is the flagship sports-operations application within the **Bulldog Stats & Sports Innovation** ecosystem. It is a free baseball and softball Statbook designed to turn every game into persistent player, team, season, and career history.

Unlike traditional scorekeeping apps, Bulldog Statbook is built on a **persistent identity and event-driven progression platform**:
* **Game → Events → Statistics → Season History → Career History → Milestones & Records → Shareable Artifacts**

---

## 🚀 Key App Capabilities (Waves 1–7)

- **Permanent Identity Model:** Strict separation between `User` account and `PlayerIdentity`. Coaches can add unclaimed players to rosters before athletes register accounts.
- **Contextual Team & Role Management:** Flexible authorization supporting `team_admin`, `coach`, `scorekeeper`, and `viewer` roles per team context.
- **Game Scheduling & Ruleset Engine:** Configurable sport rulesets (`config_json`) supporting Little League, NFHS High School Baseball, NFHS Fastpitch Softball, USSSA, and USA Softball (ASA) slowpitch formats.
- **Live Play-by-Play Scoring Interface:** Real-time logging of pitches, hits, walks, strikeouts, stolen bases, and substitutions with automated base-runner advancement and count tracking.
- **Real-Time Statistical Projections:** Raw events (`game_events`) serve as the single source of truth. Player (`game_player_stats`) and team (`game_team_stats`) statistics are dynamically derived projection caches that can be transactionally rebuilt at any time.
- **Historical CSV Imports & Provenance:** Import prior offline seasons with explicit source labeling (`historical_imports`), fidelity disclosures, and an absolute firewall blocking imported stats from granting app Experience Points (XP).
- **Milestones & Youth-Safe Share Cards:** Auto-detection of career achievements, hitting streaks, and thresholds with minor-protected share card rendering (`noindex, nofollow` meta directives).
- **Admin Duplicate Resolver:** Transaction-safe administrative merging (`MergePlayerIdentitiesAction`) to resolve split player profiles without data loss or stat drift.

---

## 🛠️ Stack & Infrastructure Architecture

- **Framework:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL on DreamHost (Beta) / SQLite (Local & Testing) with PostgreSQL-compatible schema discipline
- **Frontend:** Laravel Blade + Vanilla JS + Responsive Whalers-inspired Design Tokens (`--navy`, `--green`)
- **Key Testing:** PHPUnit integration suite (`ScoringIntegrationTest.php`) verifying 27+ assertions on live scoring and stat calculation

---

## 🏃 Quickstart Guide (GitHub Codespaces / Local Setup)

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/your-org/bulldog-statbook.git
cd bulldog-statbook
composer install
```

### 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Ensure your `.env` contains:
```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=/workspaces/Bulldog/database/database.sqlite
SESSION_DRIVER=file
```

### 3. Run Migrations & Seeders
```bash
php artisan migrate:fresh --seed
```
*This populates sports (`baseball`, `softball`) and default competition rulesets (`Little League`, `NFHS Baseball`, `NFHS Softball`, `USSSA`, `USA Softball`).*

### 4. Execute Integration Test Suite
```bash
php artisan test --filter=ScoringIntegrationTest
```

### 5. Start Development Server
```bash
php artisan serve --port=8000
```
Open your browser at `http://localhost:8000` or use your Codespace browser preview.

---

## 📁 Key File Structure

```
├── app/
│   ├── Actions/            # Transactional business workflows (Scoring, Imports, Merges)
│   ├── Http/Controllers/  # Thin Web and API controllers
│   ├── Models/             # Eloquent Models with UUID traits
│   └── Policies/           # Context-aware authorization policies (GamePolicy, TeamPolicy)
├── database/
│   ├── migrations/         # PostgreSQL-portable UUID migration schemas
│   └── seeders/            # SportSeeder and RulesetSeeder
├── resources/views/
│   ├── admin/              # Duplicate-resolver workspace
│   ├── games/              # Scheduler, visual workspace (show), live scorer (score)
│   ├── imports/            # Multipage CSV import wizard
│   ├── profile/            # Career history profile
│   ├── sharing/            # Minor-safe share card layout
│   └── teams/              # Team detail workspace & roster management
├── routes/
│   ├── api.php             # API v1 endpoints
│   └── web.php             # Session-authenticated web routes
└── tests/
    └── Feature/            # E2E and Scoring integration test suites
```

---

## 🔒 Security & Youth Safety Mandates

1. **No Public Search Indexing:** Minor profiles, player identity pages, and share cards are protected by `noindex, nofollow` directives and tokenized links.
2. **Context-Permissioned Authority:** Game scoring permissions require `team_admin`, `coach`, or `scorekeeper` roles verified server-side.
3. **Auditability:** All corrections, finalized games, historical uploads, and identity merges generate permanent audit records in `audit_logs` and `player_identity_merges`.

---

## 📄 Documentation Index

- [`ARCHITECTURE.md`](ARCHITECTURE.md) — Technical Architecture & Non-Negotiable Decision Records
- [`final-launch-readiness-checklist.md`](final-launch-readiness-checklist.md) — Pre-deployment Environment & Security Checklist
