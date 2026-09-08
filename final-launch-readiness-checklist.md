# Final Launch Readiness Checklist
**Bulldog Statbook MVP Deployment Phase**

This operational checklist is prepared specifically for deploying **Bulldog Statbook** on your live production environment (such as a DreamHost-hosted subdomain [358]), while keeping the main marketing site on WordPress [358]. It enforces database indexes, strict environment controls, security headers, and the youth-privacy architecture required by the system design [80, 102].

---

## 🌐 Section 1: Domain & Web Server Alignment
*Goal: Ensure the subdomain is cleanly isolated from WordPress and correctly routed to the Laravel public entry point.*

- [ ] **Dedicated Web Root Directory:** 
  - Subdomain (e.g., `app.bulldogstats.com` or `statbook.bulldogstats.com`) must point directly to `/home/username/bulldog-app/public` [361, 363, 364].
  - Verify that no WordPress files or direct routing rules overlap with the Bulldog Laravel codebase [361, 374].
- [ ] **HTTPS / SSL Certificate Setup:** 
  - Ensure a valid SSL certificate (such as Let's Encrypt) is fully active on the subdomain [368].
  - Force HTTPS in your Apache `.htaccess` or Nginx configuration, preventing unsecured HTTP fallback [368, 374].
- [ ] **Server PHP Configuration:** 
  - Confirm the subdomain's CLI and FPM PHP environments are running PHP 8.1+ (ideally 8.2+) [359, 360].
  - Verify standard PHP extensions required by Laravel are loaded (`ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo_sqlite`, or your production SQL engine, `session`, `xml`).

---

## 🔒 Section 2: Production Environment Variables (`.env`)
*Goal: Lock down debug capabilities, secure sessions, and align caching systems for maximum production stability.*

- [ ] **Core Application Settings:**
  ```env
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://app.bulldogstats.com
  ```
  *CRITICAL:* Double-check that `APP_DEBUG` is absolutely set to `false` so internal system call stacks are never exposed to public viewers [374].
- [ ] **Database Connection Security:**
  - Secure production-only database credentials (separate from your local test sandbox) [364, 374].
  - Double-check that the database credentials have no overlapping write access to any other production databases [364, 374].
- [ ] **Session & Cookie Protections:**
  - Ensure cookies are guarded and only accessible over HTTPS [368, 369]:
    ```env
    SESSION_DRIVER=file
    SESSION_SECURE_COOKIE=true
    SESSION_COOKIE_DOMAIN=.bulldogstats.com
    ```
    *(Note: Using a wildcard domain configuration prevents authentication session dropping when routing between pages.) [369]*
- [ ] **Cache Configuration Hardening:**
  - Build active routing, view, and config caches to bypass slow dynamic disk scans on production [367]:
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

---

## 🛡️ Section 3: Security Headers & Youth Privacy Guards
*Goal: Protect minor athletes from search engine discoverability and prevent clickjacking, XSS, or unauthorized framing.*

- [ ] **Global Security Headers (Web Server or Middleware Layer):**
  Ensure your web responses deliver the following security parameters:
  - `X-Frame-Options: SAMEORIGIN` (prevents malicious clickjacking overlays)
  - `X-Content-Type-Options: nosniff` (forces correct MIME types)
  - `Referrer-Policy: strict-origin-when-cross-origin` (masks navigation patterns)
- [ ] **Youth Privacy Protection (`noindex, nofollow`):**
  - Verify that all visual shareable cards (`share-card.blade.php`), athlete profile career pages, and public routing structures output the mandatory indexing blocks to guard youth identities from web-crawler indexing [102, 276]:
    ```html
    <meta name="robots" content="noindex, nofollow">
    ```
  - Confirm that search-engine crawling paths (`/robots.txt`) explicitly disallow searching under athlete identifiers:
    ```txt
    User-agent: *
    Disallow: /players/
    Disallow: /sharing/
    ```
- [ ] **Guardian-Scoped Authorization Enforcement:**
  - Run dynamic check verifications to verify that no unauthorized user can edit roster slots, adjust field scores, or access private relationships without passing backend policy layers [119, 127].

---

## ⚡ Section 4: Database Performance Indexes
*Goal: Guarantee sub-second database queries for play-by-play event streams and derived box scores during active games.*

Ensure your production migrations execute these essential index structures to protect high-frequency query endpoints [146, 147]:

- [ ] **Identity Lookups:**
  - `users(email)`
  - `player_identities(player_code)`
  - `guardian_relationships(guardian_user_id, player_identity_id)`
- [ ] **Roster & Context Query Paths:**
  - `team_memberships(team_id, membership_type, status)`
  - `team_role_assignments(team_id, user_id)`
- [ ] **Live Play-by-Play Event Streams:**
  - `games(home_team_id, scheduled_at)`
  - `games(away_team_id, scheduled_at)`
  - `game_events(game_id, sequence_number)` [146] *(CRITICAL: sequence must be sequentially ordered and indexed to reconstruct the scorebook chronologically!)*
  - `game_event_players(game_event_id)` [146]
- [ ] **Cache Recalculation Aggregates:**
  - `game_player_stats(game_id, player_identity_id)` [147]
  - `season_aggregates(subject_type, subject_id, season_label, stat_key)` [147]
  - `career_aggregates(subject_type, subject_id, stat_key)` [147]

---

## 📋 Section 5: Dynamic Disclaimers & Provenance Audits
*Goal: Preserve transparency between live event scoring and manual historical file uploads.*

- [ ] **Uploader Attribution Verification:**
  - Confirm that all imported files track the original filename, the uploading account, and the data fidelity level securely in the DB (`historical_imports`) [83, 148].
- [ ] **Fidelity & Experience Point Restrictions:**
  - Double-check that imported statistical totals do not grant XP ledger entries (XP is reserved strictly for live scorekeeping play) [77, 144].
- [ ] **Disclaimers Placed in Views:**
  - Ensure the system-wide visual badge disclosure statement ("*Bulldog cannot independently verify uploaded/non-official data*") renders correctly on career pages and share cards that contain imported data [75, 115].

---

## 🏃 Section 6: Launch Smoke Tests
*Goal: Verify essential workflows operate cleanly end-to-end on your live domain.*

- [ ] **Authentication & Profiles:** Register a new user and confirm that account preferences write cleanly [371].
- [ ] **Operational Team Workspace:** Create a team, assign a dynamic team role, and ensure roster slots save [371, 372].
- [ ] **Live Match Integration:** Schedule a game, select baseball or softball rules, load the dynamic roster context, and verify you can log pitches and hits from the scoring view.
- [ ] **Downstream Stat Verification:** Finalize a game and verify that individual player stats, team summaries, and achievements instantly write to the database [371].
