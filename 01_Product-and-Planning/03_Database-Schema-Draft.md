# Concrete Database Schema Draft v1  
## Bulldog Statbook / Bulldog Core

This is a **practical schema draft**, not a final migration-ready schema.

Its purpose is to translate the blueprint into a relational structure that Codex can implement and refine.

I am optimizing this draft for:

- identity integrity
- event-driven scoring
- longitudinal history
- auditability
- privacy control
- practical MVP delivery

I’ll assume a **relational database** such as PostgreSQL.

---

# 1. Schema design principles

## 1.1 Core principles
1. **One person = one user**
2. **User and PlayerIdentity are distinct**
3. **Events are the source of truth**
4. **Aggregates are derived/projection tables**
5. **Visibility and permissions are contextual**
6. **Imports must preserve provenance**
7. **Finalized records are authoritative**
8. **Historical continuity matters more than seasonal isolation**

## 1.2 Modeling stance
This draft uses:
- normalized identity and relationship tables
- event tables for game truth
- projection tables for stats/history
- JSON where flexibility is helpful, but not for everything

---

# 2. Suggested schemas / modules

If using PostgreSQL, I recommend logical separation by schema or at least by module naming.

Possible schemas:
- `identity`
- `sports`
- `competition`
- `games`
- `stats`
- `history`
- `imports`
- `sharing`
- `audit`

If you prefer one schema, keep strong naming conventions.

For readability below, I’ll use plain table names.

---

# 3. Identity tables

## 3.1 `users`
Represents a real person account.

```sql
users (
  id UUID PK,
  bulldog_user_code VARCHAR(32) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  email_verified_at TIMESTAMPTZ NULL,
  password_hash TEXT NULL,
  auth_provider VARCHAR(50) NULL,
  auth_provider_subject VARCHAR(255) NULL,
  phone VARCHAR(50) NULL,
  phone_verified_at TIMESTAMPTZ NULL,
  date_of_birth DATE NULL,
  is_minor BOOLEAN NOT NULL DEFAULT FALSE,
  account_status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### Notes
- `bulldog_user_code` is the human-facing Bulldog ID
- `is_minor` may be computed from DOB in app logic, but storing it is practical for policy checks
- if external auth is used, keep provider fields nullable

---

## 3.2 `user_profiles`

```sql
user_profiles (
  user_id UUID PK REFERENCES users(id) ON DELETE CASCADE,
  display_name VARCHAR(150) NOT NULL,
  first_name VARCHAR(100) NULL,
  last_name VARCHAR(100) NULL,
  avatar_url TEXT NULL,
  bio TEXT NULL,
  city VARCHAR(100) NULL,
  state_region VARCHAR(100) NULL,
  country VARCHAR(100) NULL,
  default_visibility_level VARCHAR(30) NOT NULL DEFAULT 'private',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

---

## 3.3 `player_identities`

```sql
player_identities (
  id UUID PK,
  player_code VARCHAR(32) UNIQUE NOT NULL,
  user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  claim_status VARCHAR(30) NOT NULL DEFAULT 'unclaimed',
  created_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  primary_sport_id UUID NULL,
  birth_year INT NULL,
  identity_status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### Notes
- `user_id` nullable supports coach-created player records before account claim
- `primary_sport_id` can be FK later once `sports` table exists
- one user may have one linked player identity in MVP, but schema can support future flexibility if needed

---

## 3.4 `coach_identities`

```sql
coach_identities (
  id UUID PK,
  coach_code VARCHAR(32) UNIQUE NOT NULL,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  identity_status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

---

## 3.5 `guardian_relationships`

```sql
guardian_relationships (
  id UUID PK,
  guardian_user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  player_identity_id UUID NOT NULL REFERENCES player_identities(id) ON DELETE CASCADE,
  relationship_type VARCHAR(50) NOT NULL,
  verification_status VARCHAR(30) NOT NULL DEFAULT 'pending',
  is_primary_guardian BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL,
  UNIQUE (guardian_user_id, player_identity_id)
)
```

### Examples
- `relationship_type`: `mother`, `father`, `guardian`, `grandparent`

---

## 3.6 `user_role_flags` (optional)
If you want convenience flags in addition to contextual roles.

```sql
user_role_flags (
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  role_type VARCHAR(30) NOT NULL,
  created_at TIMESTAMPTZ NOT NULL,
  PRIMARY KEY (user_id, role_type)
)
```

### Note
Optional. Useful for UX shortcuts, but should not replace contextual permission checks.

---

# 4. Sports structure tables

## 4.1 `sports`

```sql
sports (
  id UUID PK,
  code VARCHAR(30) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL
)
```

Examples:
- `baseball`
- `softball`

---

## 4.2 `organizations`

```sql
organizations (
  id UUID PK,
  name VARCHAR(255) NOT NULL,
  organization_type VARCHAR(50) NOT NULL,
  city VARCHAR(100) NULL,
  state_region VARCHAR(100) NULL,
  country VARCHAR(100) NULL,
  created_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

---

## 4.3 `leagues`

```sql
leagues (
  id UUID PK,
  name VARCHAR(255) NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  organization_id UUID NULL REFERENCES organizations(id) ON DELETE SET NULL,
  season_label VARCHAR(100) NULL,
  visibility_level VARCHAR(30) NOT NULL DEFAULT 'private',
  created_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

---

## 4.4 `tournaments`

```sql
tournaments (
  id UUID PK,
  name VARCHAR(255) NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  organization_id UUID NULL REFERENCES organizations(id) ON DELETE SET NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  created_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

---

## 4.5 `teams`

For MVP, this can represent the practical real-world team instance.

```sql
teams (
  id UUID PK,
  team_code VARCHAR(32) UNIQUE NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  organization_id UUID NULL REFERENCES organizations(id) ON DELETE SET NULL,
  name VARCHAR(255) NOT NULL,
  nickname VARCHAR(255) NULL,
  season_label VARCHAR(100) NULL,
  age_group VARCHAR(50) NULL,
  city VARCHAR(100) NULL,
  state_region VARCHAR(100) NULL,
  created_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  visibility_level VARCHAR(30) NOT NULL DEFAULT 'team',
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### Note
Later, you may split:
- `team_programs`
- `team_seasons`

But this draft keeps MVP practical.

---

## 4.6 `league_teams`

```sql
league_teams (
  league_id UUID NOT NULL REFERENCES leagues(id) ON DELETE CASCADE,
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  joined_at TIMESTAMPTZ NOT NULL,
  PRIMARY KEY (league_id, team_id)
)
```

---

## 4.7 `tournament_teams`

```sql
tournament_teams (
  tournament_id UUID NOT NULL REFERENCES tournaments(id) ON DELETE CASCADE,
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  joined_at TIMESTAMPTZ NOT NULL,
  PRIMARY KEY (tournament_id, team_id)
)
```

---

# 5. Team membership and roles

## 5.1 `team_memberships`

This is a crucial table.

```sql
team_memberships (
  id UUID PK,
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  membership_type VARCHAR(30) NOT NULL,
  user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  player_identity_id UUID NULL REFERENCES player_identities(id) ON DELETE SET NULL,
  coach_identity_id UUID NULL REFERENCES coach_identities(id) ON DELETE SET NULL,
  jersey_number VARCHAR(20) NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### Examples
- player membership
- coach membership
- assistant coach
- manager

### Constraint guidance
You should enforce in app or DB that:
- player memberships require `player_identity_id`
- coach memberships require `coach_identity_id` or `user_id`
- one row should not ambiguously represent unrelated roles

---

## 5.2 `team_membership_positions`

```sql
team_membership_positions (
  id UUID PK,
  team_membership_id UUID NOT NULL REFERENCES team_memberships(id) ON DELETE CASCADE,
  position_code VARCHAR(20) NOT NULL,
  created_at TIMESTAMPTZ NOT NULL
)
```

Examples:
- `P`, `C`, `1B`, `SS`, etc.

---

## 5.3 `team_role_assignments`

This handles admin/coach/scorekeeper permissions in team context.

```sql
team_role_assignments (
  id UUID PK,
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  role_type VARCHAR(30) NOT NULL,
  granted_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL,
  UNIQUE (team_id, user_id, role_type)
)
```

Examples:
- `team_admin`
- `coach`
- `scorekeeper`
- `viewer`

---

# 6. Ruleset tables

## 6.1 `rulesets`

```sql
rulesets (
  id UUID PK,
  sport_id UUID NOT NULL REFERENCES sports(id),
  name VARCHAR(255) NOT NULL,
  code VARCHAR(50) UNIQUE NOT NULL,
  config_json JSONB NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### Use
Store things like:
- DH/DP/Flex support
- continuous batting order
- courtesy runner rules
- re-entry settings

---

# 7. Game tables

## 7.1 `games`

```sql
games (
  id UUID PK,
  game_code VARCHAR(32) UNIQUE NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  home_team_id UUID NOT NULL REFERENCES teams(id),
  away_team_id UUID NOT NULL REFERENCES teams(id),
  league_id UUID NULL REFERENCES leagues(id) ON DELETE SET NULL,
  tournament_id UUID NULL REFERENCES tournaments(id) ON DELETE SET NULL,
  ruleset_id UUID NULL REFERENCES rulesets(id) ON DELETE SET NULL,
  scheduled_at TIMESTAMPTZ NULL,
  location_name VARCHAR(255) NULL,
  ownership_team_id UUID NOT NULL REFERENCES teams(id),
  status VARCHAR(30) NOT NULL DEFAULT 'draft',
  created_by_user_id UUID NOT NULL REFERENCES users(id),
  finalized_at TIMESTAMPTZ NULL,
  finalized_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  suspended_at TIMESTAMPTZ NULL,
  resumed_at TIMESTAMPTZ NULL,
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### Status examples
- `draft`
- `in_progress`
- `review`
- `finalized`
- `corrected`
- `suspended`

### Rule
`ownership_team_id` should default to `home_team_id`

---

## 7.2 `game_editors`

```sql
game_editors (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  editor_role VARCHAR(30) NOT NULL,
  granted_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  UNIQUE (game_id, user_id, editor_role)
)
```

Examples:
- `scorekeeper`
- `reviewer`
- `owner`

---

## 7.3 `game_roster_entries`

```sql
game_roster_entries (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  player_identity_id UUID NOT NULL REFERENCES player_identities(id) ON DELETE CASCADE,
  team_membership_id UUID NULL REFERENCES team_memberships(id) ON DELETE SET NULL,
  roster_status VARCHAR(30) NOT NULL DEFAULT 'active',
  eligible_to_play BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMPTZ NOT NULL,
  UNIQUE (game_id, team_id, player_identity_id)
)
```

---

## 7.4 `lineup_entries`

```sql
lineup_entries (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  player_identity_id UUID NOT NULL REFERENCES player_identities(id) ON DELETE CASCADE,
  batting_order_slot INT NOT NULL,
  lineup_status VARCHAR(30) NOT NULL DEFAULT 'active',
  entered_at_event_id UUID NULL,
  exited_at_event_id UUID NULL,
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### Note
You may later need rule-aware uniqueness/validation here depending on lineup mode.

---

## 7.5 `defensive_assignments`

```sql
defensive_assignments (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  player_identity_id UUID NOT NULL REFERENCES player_identities(id) ON DELETE CASCADE,
  position_code VARCHAR(20) NOT NULL,
  effective_from_event_id UUID NULL,
  effective_to_event_id UUID NULL,
  created_at TIMESTAMPTZ NOT NULL
)
```

---

# 8. Event stream tables

## 8.1 `game_events`

This is the core truth table.

```sql
game_events (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  sequence_number INT NOT NULL,
  event_family VARCHAR(30) NOT NULL,
  event_type VARCHAR(50) NOT NULL,
  event_subtype VARCHAR(50) NULL,
  team_at_bat_id UUID NULL REFERENCES teams(id) ON DELETE SET NULL,
  offense_player_id UUID NULL REFERENCES player_identities(id) ON DELETE SET NULL,
  pitcher_player_id UUID NULL REFERENCES player_identities(id) ON DELETE SET NULL,
  inning_number INT NULL,
  half_inning VARCHAR(10) NULL,
  outs_before INT NULL,
  outs_after INT NULL,
  balls_before INT NULL,
  strikes_before INT NULL,
  balls_after INT NULL,
  strikes_after INT NULL,
  home_score_before INT NULL,
  away_score_before INT NULL,
  home_score_after INT NULL,
  away_score_after INT NULL,
  base_state_before JSONB NULL,
  base_state_after JSONB NULL,
  payload_json JSONB NOT NULL DEFAULT '{}'::jsonb,
  created_by_user_id UUID NOT NULL REFERENCES users(id),
  superseded_by_event_id UUID NULL REFERENCES game_events(id) ON DELETE SET NULL,
  is_voided BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL,
  UNIQUE (game_id, sequence_number)
)
```

### Examples
`event_family`:
- `plate_appearance`
- `baserunning`
- `defense`
- `pitching`
- `game_admin`
- `correction`

### Note
`payload_json` is where specialized event details live.

---

## 8.2 `game_event_players`

Useful for multi-player event attribution.

```sql
game_event_players (
  id UUID PK,
  event_id UUID NOT NULL REFERENCES game_events(id) ON DELETE CASCADE,
  player_identity_id UUID NOT NULL REFERENCES player_identities(id) ON DELETE CASCADE,
  player_role_in_event VARCHAR(50) NOT NULL,
  team_id UUID NULL REFERENCES teams(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL
)
```

Examples:
- `runner`
- `fielder`
- `assisting_fielder`
- `putout_fielder`
- `sub_in`
- `sub_out`

This avoids overly bloated columns in `game_events`.

---

## 8.3 `game_state_snapshots`

```sql
game_state_snapshots (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  event_id UUID NULL REFERENCES game_events(id) ON DELETE SET NULL,
  inning_number INT NOT NULL,
  half_inning VARCHAR(10) NOT NULL,
  outs INT NOT NULL,
  balls INT NOT NULL,
  strikes INT NOT NULL,
  home_score INT NOT NULL,
  away_score INT NOT NULL,
  bases_json JSONB NOT NULL,
  current_batter_id UUID NULL REFERENCES player_identities(id) ON DELETE SET NULL,
  current_pitcher_id UUID NULL REFERENCES player_identities(id) ON DELETE SET NULL,
  defensive_alignment_json JSONB NULL,
  lineup_pointer_json JSONB NULL,
  created_at TIMESTAMPTZ NOT NULL
)
```

---

# 9. Review, finalization, correction tables

## 9.1 `game_review_items`

```sql
game_review_items (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  review_type VARCHAR(50) NOT NULL,
  severity VARCHAR(20) NOT NULL,
  description TEXT NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'open',
  related_event_id UUID NULL REFERENCES game_events(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL,
  resolved_at TIMESTAMPTZ NULL,
  resolved_by_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL
)
```

---

## 9.2 `game_finalization_records`

```sql
game_finalization_records (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  finalized_by_user_id UUID NOT NULL REFERENCES users(id),
  finalization_notes TEXT NULL,
  created_at TIMESTAMPTZ NOT NULL,
  UNIQUE (game_id)
)
```

---

## 9.3 `game_correction_audit`

```sql
game_correction_audit (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  corrected_by_user_id UUID NOT NULL REFERENCES users(id),
  correction_type VARCHAR(50) NOT NULL,
  target_event_id UUID NULL REFERENCES game_events(id) ON DELETE SET NULL,
  notes TEXT NULL,
  created_at TIMESTAMPTZ NOT NULL
)
```

---

# 10. Stats projection tables

These are derived and rebuildable.

## 10.1 `game_player_stats`

```sql
game_player_stats (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  player_identity_id UUID NOT NULL REFERENCES player_identities(id) ON DELETE CASCADE,
  stat_key VARCHAR(50) NOT NULL,
  stat_value NUMERIC(12,4) NOT NULL,
  official_status VARCHAR(30) NOT NULL DEFAULT 'provisional',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL,
  UNIQUE (game_id, player_identity_id, stat_key)
)
```

---

## 10.2 `game_team_stats`

```sql
game_team_stats (
  id UUID PK,
  game_id UUID NOT NULL REFERENCES games(id) ON DELETE CASCADE,
  team_id UUID NOT NULL REFERENCES teams(id) ON DELETE CASCADE,
  stat_key VARCHAR(50) NOT NULL,
  stat_value NUMERIC(12,4) NOT NULL,
  official_status VARCHAR(30) NOT NULL DEFAULT 'provisional',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL,
  UNIQUE (game_id, team_id, stat_key)
)
```

---

## 10.3 `season_aggregates`

```sql
season_aggregates (
  id UUID PK,
  subject_type VARCHAR(30) NOT NULL,
  subject_id UUID NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  season_label VARCHAR(100) NOT NULL,
  team_id UUID NULL REFERENCES teams(id) ON DELETE SET NULL,
  stat_key VARCHAR(50) NOT NULL,
  stat_value NUMERIC(12,4) NOT NULL,
  source_type VARCHAR(30) NOT NULL DEFAULT 'bulldog_derived',
  official_status VARCHAR(30) NOT NULL DEFAULT 'official',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### `subject_type` examples
- `player`
- `team`
- `coach`

---

## 10.4 `career_aggregates`

```sql
career_aggregates (
  id UUID PK,
  subject_type VARCHAR(30) NOT NULL,
  subject_id UUID NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  stat_key VARCHAR(50) NOT NULL,
  stat_value NUMERIC(12,4) NOT NULL,
  source_type VARCHAR(30) NOT NULL DEFAULT 'mixed',
  official_status VARCHAR(30) NOT NULL DEFAULT 'official',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL,
  UNIQUE (subject_type, subject_id, sport_id, stat_key)
)
```

---

# 11. Historical import tables

## 11.1 `historical_imports`

```sql
historical_imports (
  id UUID PK,
  import_type VARCHAR(50) NOT NULL,
  uploaded_by_user_id UUID NOT NULL REFERENCES users(id),
  context_type VARCHAR(30) NOT NULL,
  context_id UUID NOT NULL,
  source_label VARCHAR(255) NOT NULL,
  fidelity_level VARCHAR(30) NOT NULL,
  verification_status VARCHAR(30) NOT NULL DEFAULT 'unverified',
  notes TEXT NULL,
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### Examples
- `context_type`: `player`, `team`, `league`
- `fidelity_level`:
  - `season_totals`
  - `box_score`
  - `game_log`
  - `play_by_play`
  - `event_complete`

---

## 11.2 `imported_stat_lines`

```sql
imported_stat_lines (
  id UUID PK,
  historical_import_id UUID NOT NULL REFERENCES historical_imports(id) ON DELETE CASCADE,
  subject_type VARCHAR(30) NOT NULL,
  subject_id UUID NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  season_label VARCHAR(100) NULL,
  game_date DATE NULL,
  team_id UUID NULL REFERENCES teams(id) ON DELETE SET NULL,
  stat_blob_json JSONB NOT NULL,
  source_row_identifier VARCHAR(255) NULL,
  created_at TIMESTAMPTZ NOT NULL
)
```

### Why JSON here?
Imported data can vary widely in completeness and shape.

---

## 11.3 `import_audit_logs`

```sql
import_audit_logs (
  id UUID PK,
  historical_import_id UUID NOT NULL REFERENCES historical_imports(id) ON DELETE CASCADE,
  action_type VARCHAR(50) NOT NULL,
  action_by_user_id UUID NOT NULL REFERENCES users(id),
  notes TEXT NULL,
  created_at TIMESTAMPTZ NOT NULL
)
```

---

# 12. Recognition tables

## 12.1 `milestones`

```sql
milestones (
  id UUID PK,
  subject_type VARCHAR(30) NOT NULL,
  subject_id UUID NOT NULL,
  milestone_type VARCHAR(50) NOT NULL,
  scope_type VARCHAR(30) NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  value_reached NUMERIC(12,4) NULL,
  related_stat_key VARCHAR(50) NULL,
  source_type VARCHAR(30) NOT NULL,
  fidelity_level VARCHAR(30) NOT NULL,
  official_status VARCHAR(30) NOT NULL DEFAULT 'official',
  detected_at TIMESTAMPTZ NOT NULL,
  metadata_json JSONB NOT NULL DEFAULT '{}'::jsonb
)
```

---

## 12.2 `records`

```sql
records (
  id UUID PK,
  record_scope VARCHAR(30) NOT NULL,
  subject_type VARCHAR(30) NOT NULL,
  subject_id UUID NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  stat_key VARCHAR(50) NOT NULL,
  record_value NUMERIC(12,4) NOT NULL,
  effective_date DATE NULL,
  source_type VARCHAR(30) NOT NULL,
  fidelity_level VARCHAR(30) NOT NULL,
  official_status VARCHAR(30) NOT NULL DEFAULT 'official',
  metadata_json JSONB NOT NULL DEFAULT '{}'::jsonb,
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

---

## 12.3 `achievement_definitions`

```sql
achievement_definitions (
  id UUID PK,
  code VARCHAR(50) UNIQUE NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  rule_json JSONB NOT NULL,
  xp_value INT NOT NULL DEFAULT 0,
  is_repeatable BOOLEAN NOT NULL DEFAULT FALSE,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### MVP note
Use system-defined rows only.

---

## 12.4 `achievement_awards`

```sql
achievement_awards (
  id UUID PK,
  achievement_definition_id UUID NOT NULL REFERENCES achievement_definitions(id),
  subject_type VARCHAR(30) NOT NULL,
  subject_id UUID NOT NULL,
  source_type VARCHAR(30) NOT NULL,
  fidelity_level VARCHAR(30) NOT NULL,
  official_status VARCHAR(30) NOT NULL DEFAULT 'official',
  awarded_at TIMESTAMPTZ NOT NULL,
  metadata_json JSONB NOT NULL DEFAULT '{}'::jsonb
)
```

---

## 12.5 `streaks`

```sql
streaks (
  id UUID PK,
  subject_type VARCHAR(30) NOT NULL,
  subject_id UUID NOT NULL,
  streak_type VARCHAR(50) NOT NULL,
  sport_id UUID NOT NULL REFERENCES sports(id),
  current_value INT NOT NULL DEFAULT 0,
  best_value INT NOT NULL DEFAULT 0,
  start_game_id UUID NULL REFERENCES games(id) ON DELETE SET NULL,
  end_game_id UUID NULL REFERENCES games(id) ON DELETE SET NULL,
  official_status VARCHAR(30) NOT NULL DEFAULT 'official',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

---

## 12.6 `timeline_entries`

```sql
timeline_entries (
  id UUID PK,
  subject_type VARCHAR(30) NOT NULL,
  subject_id UUID NOT NULL,
  sport_id UUID NULL REFERENCES sports(id) ON DELETE SET NULL,
  entry_type VARCHAR(50) NOT NULL,
  entry_date DATE NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  source_type VARCHAR(30) NOT NULL,
  source_ref_type VARCHAR(50) NULL,
  source_ref_id UUID NULL,
  official_status VARCHAR(30) NOT NULL DEFAULT 'official',
  visibility_level VARCHAR(30) NOT NULL DEFAULT 'private',
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

---

# 13. XP and progression tables

Since XP is usage-based, not import-based:

## 13.1 `xp_ledger`

```sql
xp_ledger (
  id UUID PK,
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  event_code VARCHAR(50) NOT NULL,
  points INT NOT NULL,
  source_type VARCHAR(30) NOT NULL,
  source_ref_type VARCHAR(50) NULL,
  source_ref_id UUID NULL,
  created_at TIMESTAMPTZ NOT NULL
)
```

### Important
Do not award XP from imported stat lines.

---

## 13.2 `user_progression`

```sql
user_progression (
  user_id UUID PK REFERENCES users(id) ON DELETE CASCADE,
  total_xp INT NOT NULL DEFAULT 0,
  level_number INT NOT NULL DEFAULT 1,
  cosmetics_unlocked_json JSONB NOT NULL DEFAULT '[]'::jsonb,
  updated_at TIMESTAMPTZ NOT NULL
)
```

---

# 14. Sharing and visibility tables

## 14.1 `visibility_rules`

```sql
visibility_rules (
  id UUID PK,
  object_type VARCHAR(50) NOT NULL,
  object_id UUID NOT NULL,
  visibility_level VARCHAR(30) NOT NULL,
  effective_for_minor BOOLEAN NOT NULL DEFAULT TRUE,
  set_by_user_id UUID NOT NULL REFERENCES users(id),
  created_at TIMESTAMPTZ NOT NULL,
  updated_at TIMESTAMPTZ NOT NULL
)
```

### Examples
`object_type`:
- `player_profile`
- `player_card`
- `team_page`
- `timeline_entry`
- `milestone`
- `achievement`

---

## 14.2 `share_links`

```sql
share_links (
  id UUID PK,
  object_type VARCHAR(50) NOT NULL,
  object_id UUID NOT NULL,
  token VARCHAR(128) UNIQUE NOT NULL,
  created_by_user_id UUID NOT NULL REFERENCES users(id),
  allow_authenticated_only BOOLEAN NOT NULL DEFAULT FALSE,
  expires_at TIMESTAMPTZ NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMPTZ NOT NULL
)
```

---

# 15. Audit tables

## 15.1 `audit_logs`

```sql
audit_logs (
  id UUID PK,
  actor_user_id UUID NULL REFERENCES users(id) ON DELETE SET NULL,
  action_type VARCHAR(100) NOT NULL,
  object_type VARCHAR(50) NOT NULL,
  object_id UUID NOT NULL,
  metadata_json JSONB NOT NULL DEFAULT '{}'::jsonb,
  created_at TIMESTAMPTZ NOT NULL
)
```

### Use for
- permission changes
- visibility changes
- claim actions
- roster actions
- imports
- game finalization
- corrections

---

# 16. Suggested indexes

Not exhaustive, but important.

## Identity
- `users(email)`
- `users(bulldog_user_code)`
- `player_identities(player_code)`
- `player_identities(user_id)`
- `coach_identities(coach_code)`
- `guardian_relationships(guardian_user_id)`
- `guardian_relationships(player_identity_id)`

## Teams
- `teams(team_code)`
- `teams(organization_id)`
- `team_memberships(team_id, membership_type, status)`
- `team_memberships(player_identity_id)`
- `team_role_assignments(team_id, user_id)`

## Games
- `games(home_team_id, scheduled_at)`
- `games(away_team_id, scheduled_at)`
- `games(status)`
- `game_events(game_id, sequence_number)`
- `game_events(game_id, inning_number, half_inning)`
- `game_event_players(event_id)`
- `game_state_snapshots(game_id, created_at DESC)`

## Stats/history
- `game_player_stats(game_id, player_identity_id)`
- `season_aggregates(subject_type, subject_id, season_label, stat_key)`
- `career_aggregates(subject_type, subject_id, stat_key)`
- `milestones(subject_type, subject_id, detected_at DESC)`
- `records(subject_type, subject_id, stat_key)`
- `timeline_entries(subject_type, subject_id, entry_date DESC)`

## Imports/sharing
- `historical_imports(context_type, context_id)`
- `share_links(token)`

---

# 17. Important constraints and rules

## 17.1 User vs player identity
Do **not** collapse `users` and `player_identities` into one table.

That distinction is mandatory.

## 17.2 Imported data provenance
Every imported stat line must be traceable to:
- who uploaded it
- what source it came from
- what fidelity level it has

## 17.3 Derived stats are rebuildable
Projection tables like `game_player_stats`, `season_aggregates`, `career_aggregates` should be treated as:
- cache/projection tables
- rebuildable from source truth and imports

## 17.4 Official finalization
Only finalized games should populate official aggregate layers.

## 17.5 Controlled sharing
Share links must not imply search discoverability.

---

# 18. Recommended enum sets

You can use DB enums or lookup tables.

## Suggested enums
### `account_status`
- `active`
- `pending`
- `suspended`
- `deleted`

### `claim_status`
- `unclaimed`
- `pending`
- `claimed`
- `disputed`

### `visibility_level`
- `private`
- `team`
- `authenticated_shared`
- `share_link`
- `public_non_indexed` (probably later)

### `game_status`
- `draft`
- `in_progress`
- `review`
- `finalized`
- `corrected`
- `suspended`

### `official_status`
- `provisional`
- `official`
- `superseded`
- `revoked`

### `source_type`
- `bulldog_derived`
- `uploaded_import`
- `manual_historical`
- `verified_import`
- `mixed`

### `fidelity_level`
- `season_totals`
- `box_score`
- `game_log`
- `play_by_play`
- `event_complete`

---

# 19. Near-term migration order

Here is the order I’d actually create tables in.

## Wave 1
- `users`
- `user_profiles`
- `sports`
- `player_identities`
- `coach_identities`
- `guardian_relationships`

## Wave 2
- `organizations`
- `leagues`
- `tournaments`
- `teams`
- `team_memberships`
- `team_membership_positions`
- `team_role_assignments`

## Wave 3
- `rulesets`
- `games`
- `game_editors`
- `game_roster_entries`
- `lineup_entries`
- `defensive_assignments`

## Wave 4
- `game_events`
- `game_event_players`
- `game_state_snapshots`
- `game_review_items`
- `game_finalization_records`
- `game_correction_audit`

## Wave 5
- `game_player_stats`
- `game_team_stats`
- `season_aggregates`
- `career_aggregates`

## Wave 6
- `historical_imports`
- `imported_stat_lines`
- `import_audit_logs`

## Wave 7
- `milestones`
- `records`
- `achievement_definitions`
- `achievement_awards`
- `streaks`
- `timeline_entries`

## Wave 8
- `xp_ledger`
- `user_progression`
- `visibility_rules`
- `share_links`
- `audit_logs`

---

# 20. Recommended simplifications for first schema pass

To avoid overbuilding too early, I would simplify these initially:

## Keep simple now
- use `teams` as practical team instance
- use `season_label` instead of a full seasons table initially
- use `payload_json` for specialized event details
- use generic `subject_type + subject_id` for milestones/records/timeline
- use `stat_key + stat_value` aggregate tables

## Defer until needed
- team program vs team season split
- fully normalized stat definition catalog
- multi-sport polymorphic competition hierarchy
- advanced ranking/scouting models
- social graph tables

---

# 21. Schema risks to watch

## Risk 1
Overusing JSON and losing query power.

## Risk 2
Over-normalizing event details before the scoring engine is stable.

## Risk 3
Letting aggregate tables drift from rebuildable truth.

## Risk 4
Not modeling permissions/visibility strongly enough.

## Risk 5
Forgetting duplicate player identity resolution later.

You may eventually need:
- `identity_merge_requests`
- `identity_aliases`
but not necessarily in first pass.

---

