# AGENTS.md

## Project identity

Bulldog is the Laravel backend/application repository for Statbook and Momentum services.

## Stack

- Laravel 12
- PHP 8.2+
- Laravel Sanctum
- Filament v3 is intended, but verify the dependency manifest before assuming it is installed
- Blade
- Alpine.js
- Tailwind CSS
- Vite
- Eloquent
- PHPUnit

`bulldogstats.com` runs on WordPress and is completely separate from this repository.

Momentum is a separate repository: `rothb6456-lang/momentum-train`.

## Read first

Before architecture or API changes, inspect `ARCHITECTURE.md`, `routes/api.php`, `routes/auth.php`, current migrations, relevant models, controllers/actions/requests, and tests.

Current code and migrations take precedence over stale prototype documents.

## API rules

- Preserve the `/api/v1` boundary.
- Verify routes before creating or consuming endpoints.
- Sanctum protects the current versioned training API.
- Never invent an authentication endpoint.
- Treat client/backend contract changes as deliberate changes.

## Database rules

- Inspect the complete current migration chain before querying a column.
- A column in an old migration may no longer exist.
- Never rewrite an applied migration.
- Prefer transactional application actions for multi-record state changes.
- Keep destructive identity workflows auditable.

## Momentum integration

Momentum is a local-first vanilla JS PWA with no build step. It must remain useful without a live Bulldog backend. Do not move local session persistence into the backend merely to simplify synchronization.

## Known pitfalls

### 1. Invalid Momentum login endpoint

Momentum previously called `POST /api/v1/auth/login`. That endpoint is not currently defined by Bulldog's API route table. The browser `/login` route is a different authentication surface.

### 2. bindLog() scope regression

A `bindLog()` binding was accidentally placed outside its intended execution block and failed to run. When changing event bindings, inspect braces and execution scope, verify the DOM exists, and exercise the affected view.

### 3. Database column drift

An API controller failed after querying a column that a later migration had dropped. Before changing a query, inspect the current migration chain, current schema/model, and tests.

### 4. Prototype/documentation drift

Historical handoff/prototype files are not automatically architectural authority. Current code, migrations, routes, and ADRs take precedence.

### 5. Exercise identity drift

Exercise names occur across cards, history, API catalogs, and personalization. Preserve canonical exercise identity and normalize aliases deliberately.

## Change discipline

Make the smallest coherent change. For API changes, verify both client and backend contracts. For schema changes, add a new migration and update dependent code/tests. For durable architectural decisions, update `ARCHITECTURE.md` and its ADR list.

## Validation

```bash
php artisan test
```

For cross-repository changes, validate both repositories.
