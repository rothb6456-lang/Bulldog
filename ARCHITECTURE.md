# Bulldog Architecture

> Status: Active
> Scope: Laravel 12 application/backend for Statbook and Momentum services

## 1. System boundary

```text
bulldogstats.com
  WordPress
  separate public site
        |
        v
Bulldog Laravel 12
  +-- Statbook web application
  +-- Statbook/API services
  +-- Momentum training API
        ^
        |
Momentum PWA
  separate repository
  local-first, no build step
```

The WordPress site is not a Laravel frontend or runtime dependency.

## 2. Application layers

Presentation uses Blade, Alpine.js, Tailwind CSS, and Vite. Application/domain logic uses Actions, models, policies, validation, and services. The API exposes explicit versioned contracts under `/api/v1`, with Sanctum protecting authenticated training routes. Persistence is governed by the current migration chain and Eloquent models.

## 3. Statbook event architecture

```text
Play-by-play event
  -> game_events
      -> game state
      -> player/team stat projections
      -> finalization
      -> season/career aggregates
      -> milestones/share artifacts
```

Raw game events are authoritative. Derived statistics are rebuildable projections.

## 4. Momentum architecture

```text
Momentum PWA
  +-- local planning/logging
  +-- localStorage
  +-- Markdown reference data
  +-- service-worker offline shell
  +-- optional authenticated API
             |
             v
       Bulldog /api/v1
         + exercises
         + body structures
         + training sessions
         + Coach AI
```

Core gym-floor operation must remain usable without the API.

## 5. Authentication boundary

Browser sessions use `/login`; Momentum uses Sanctum tokens from `/api/v1/auth/login` or the one-use `/api/v1/auth/exchange` bridge. The bridge uses an expiring URL fragment code, removes it immediately in Momentum, and exchanges it without replaying a password. CORS permits the configured Momentum origin only.

## 6. Migration discipline

When a migration drops or renames a column, all dependent queries must be reconciled with the resulting schema. Inspect migrations, models, controllers, requests, seeders, and tests before changing database-backed behavior. Never rewrite an already-applied historical migration.

## 7. Active ADRs

### ADR-001: Separate User and PlayerIdentity
Authenticated accounts and sports participant identities are distinct concepts.

### ADR-002: Event-driven Statbook scoring
Raw game events are authoritative and statistical tables are rebuildable projections.

### ADR-003: Configurable sport rulesets
Sport/league behavior is represented through configurable ruleset data.

### ADR-004: Historical import provenance
Imported historical data retains source/fidelity metadata and remains distinct from live-scored provenance.

### ADR-005: Youth privacy and protected sharing
Youth-facing records and share artifacts use controlled access and anti-indexing safeguards.

### ADR-006: Transaction-safe identity merging
Duplicate player identities are merged transactionally with auditability.

### ADR-007: Momentum as a separate local-first client
Momentum remains a separate repository and must remain useful without a live Bulldog dependency.

### ADR-008: Versioned training API
Momentum training services are exposed under `/api/v1` as an explicit client/backend contract.

### ADR-009: Canonical exercise identity
Exercise names are normalized across prescribed cards, history, library data, and API records.

### ADR-010: Training synchronization creates server records
A completed local Momentum session may be posted to Bulldog while local persistence remains primary.

### ADR-011: Personalization as structured domain data
Experience, goals, equipment, and guardrails are structured training concepts rather than UI-only rules.

### ADR-012: Coach AI as a backend boundary
Coach card generation is exposed through Bulldog rather than requiring backend credentials in Momentum.

## 8. Deployment boundary

### ADR-013: Adult accounts and confirmed guardian access
The beta accepts active adult accounts. Youth identities remain private and unclaimed. An invited guardian verifies their own account, accepts an invitation, and receives access only after a different authorized team administrator confirms. Training is visible to its owner or verified guardian, rather than every roster manager.

### ADR-014: Practice isolation and retry-safe training
Practice games set `is_demo`; real scoring projections are retained for the practice box score, while season/career compilation and achievement triggers exclude practice. Training uses PlayerIdentity UUIDs and a unique player/client-session key to make repeated uploads idempotent.

### ADR-015: Separate local workspaces per account
Momentum archives each account's local workspace when switching accounts and restores it only for that account. Catalog metadata is shared; tokens are not archived. Unknown exercise metadata never qualifies a card as bodyweight-only.

```text
bulldogstats.com
  WordPress

Bulldog
  Laravel application/API

train.bulldogstats.com
  Momentum static PWA
```

## 9. Change checklist

Before structural changes:
- identify the owning repository
- determine whether behavior is local-first or server-authoritative
- inspect routes and current storage/schema
- preserve API versioning
- update an ADR when a durable decision changes
- add regression coverage for known failure modes
- do not restore deprecated prototype architecture merely because stale documents mention it
