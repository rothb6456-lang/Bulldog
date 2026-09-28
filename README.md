# Bulldog

Bulldog is the Laravel application and backend platform for the Bulldog ecosystem, supporting both Statbook and Momentum training services.

> `bulldogstats.com` is a separate WordPress site and is completely separate from this repository.

## Current scope

### Statbook
- Baseball and softball teams, rosters, games, scoring, rulesets, imports, statistics, identity, milestones, and sharing
- Separate `User` and `PlayerIdentity` identity model
- Event-driven scoring with rebuildable projections
- Configurable sport rulesets
- Historical imports with provenance and XP safeguards
- Transaction-safe identity merging
- Youth privacy and controlled sharing

### Momentum services
- Exercise and body-structure catalogs
- Training sessions and sets
- Training personalization, goals, equipment, and guardrails
- PR and achievement support
- Coach AI card generation
- Sanctum-protected versioned API

## Stack

- PHP 8.2+
- Laravel 12
- Laravel Sanctum 4.3
- Blade
- Alpine.js
- Tailwind CSS
- Vite 7
- Eloquent
- PHPUnit 11
- SQLite for local development/testing by default

Filament v3 is part of the intended Bulldog application stack, but the current `composer.json` does not declare `filament/filament`. Do not change that dependency as part of documentation work.

## Repository boundaries

```text
bulldogstats.com
  WordPress public/marketing site
  Separate codebase and deployment

Bulldog
  Laravel 12 application
  + Statbook
  + Statbook API
  + Momentum training API

momentum-train
  Separate static vanilla-JS PWA
  + local-first planning/logging
  + optional Bulldog API integration
```

## Installation

```bash
git clone https://github.com/rothb6456-lang/Bulldog.git
cd Bulldog
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm run build
```

The checked-in `.env.example` uses SQLite via `DB_CONNECTION=sqlite`.

## Local development

```bash
composer run dev
```

Server only:
```bash
php artisan serve
```

Vite only:
```bash
npm run dev
```

## Testing

```bash
php artisan test
php artisan test --filter=ScoringIntegrationTest
```

## API boundary

Current Momentum training routes are Sanctum-protected and versioned under `/api/v1`:

```text
GET  /api/v1/training/exercises
GET  /api/v1/training/body-structures
POST /api/v1/training/body-structures/{bodyStructure}/learned
GET  /api/v1/training/sessions
POST /api/v1/training/sessions
GET  /api/v1/training/sessions/{session}
POST /api/v1/training/coach/generate-card
```

Do not infer an endpoint from client expectations or conventional Laravel naming. Inspect `routes/api.php`.

## Authentication boundary

Browser authentication and API authentication are separate.

`routes/auth.php` provides the browser session login route at `/login`. The API route table does not currently define `/api/v1/auth/login`.

Any token-issuance endpoint must therefore be an explicit API decision.

## Database discipline

Before querying a column, inspect the current migration chain and confirm the column exists in the current schema. Add a new migration for schema changes and never rewrite an already-applied migration.

## Documentation

Read [ARCHITECTURE.md](ARCHITECTURE.md) before structural changes and [AGENTS.md](AGENTS.md) before agent-driven changes.
