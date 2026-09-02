# Bulldog DreamHost Laravel Deployment Checklist v1  
## Subdomain Web App Deployment for Beta

This checklist is for deploying **Bulldog Statbook** as a **separate Laravel app** on a DreamHost-hosted subdomain, while keeping WordPress separate.

It assumes:

- main site remains WordPress
- Bulldog is a separate Laravel application
- beta database is MySQL on DreamHost
- app is used as a responsive web app and can be saved to home screen
- future PostgreSQL migration remains possible

---

# 1. Target deployment shape

## Recommended domain structure
- `www.yourdomain.com` → WordPress
- `app.yourdomain.com` or `statbook.yourdomain.com` → Laravel Bulldog app

## Recommended beta hosting goal
Bulldog should run as its own web root / subdomain app, not inside the WordPress install.

---

# 2. Confirm DreamHost hosting capability first

Before deployment, verify these in DreamHost:

## Check 1 — PHP version
Laravel requires a modern PHP version.

You want something like:
- PHP 8.1+
- ideally PHP 8.2 if supported

## Check 2 — SSH access
Strongly recommended.

You want SSH because it makes:
- Composer install
- artisan commands
- cache clears
- migrations

much easier.

## Check 3 — Composer availability
You need a way to run:
- `composer install`
- `php artisan ...`

Even if local build/upload is possible, SSH + Composer is much better.

## Check 4 — MySQL database access
Confirm:
- DB hostname
- DB name
- DB username
- DB password
- database user permissions

## Check 5 — subdomain setup
Confirm you can create:
- `app.yourdomain.com`
or
- `statbook.yourdomain.com`

with its own directory.

---

# 3. Create the subdomain in DreamHost

In DreamHost panel:

## Add a fully hosted subdomain
Example:
- `app.yourdomain.com`

DreamHost will assign a directory, often something like:
- `/home/username/app.yourdomain.com/`

This directory becomes the deployment location.

## Important
Do **not** point the Bulldog app subdomain to the WordPress directory.

Keep it separate.

---

# 4. Understand Laravel document root requirement

Laravel should **not** expose the entire project directory publicly.

The public web root should point to:

# `public/`

inside the Laravel project.

That means the ideal structure is:

```text
/home/username/bulldog-app/
  app/
  bootstrap/
  config/
  database/
  public/
  resources/
  routes/
  storage/
  vendor/
  artisan
```

And the subdomain document root should serve:
```text
/home/username/bulldog-app/public
```

---

# 5. DreamHost constraint to check carefully

Some shared hosts make custom document-root control awkward.

## Best case
DreamHost lets you set the subdomain web directory directly to:
- `/home/username/bulldog-app/public`

## If yes:
That is ideal.

## If no:
You may need one of these workarounds:
- place Laravel project in one directory and symlink/copy `public` contents into subdomain root
- or structure deployment so subdomain root effectively behaves like Laravel `public`

### But preferred:
**Set document root to Laravel `/public` if DreamHost allows it.**

---

# 6. Recommended deployment directory structure

I recommend this layout:

```text
/home/username/
  wordpress-site/
  bulldog-app/
    app/
    bootstrap/
    config/
    database/
    public/
    resources/
    routes/
    storage/
    vendor/
    .env
    artisan
```

If document root can be set:
- subdomain root → `/home/username/bulldog-app/public`

This is the cleanest arrangement.

---

# 7. Create a separate DreamHost MySQL database for Bulldog

Since you already have databases, create or dedicate one specifically for Bulldog beta.

## Recommended
Use a separate DB, e.g.
- `bulldog_beta`

Do not reuse the WordPress database.

## You need:
- DB host
- DB name
- DB user
- DB password

Keep these for `.env`.

---

# 8. Local vs server build strategy

There are two ways to deploy Laravel.

## Option A — Build locally, upload to server
You:
- build project locally
- run composer locally
- upload project files
- configure `.env`
- run artisan on server if possible

## Option B — Deploy directly on server via SSH
You:
- clone/upload project to server
- run `composer install`
- run `php artisan key:generate`
- run migrations on server

## Recommendation
For DreamHost beta:
**Use SSH if available.**

---

# 9. Required `.env` settings

Your Laravel `.env` will need at minimum:

```env
APP_NAME=Bulldog
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://app.yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=your-dreamhost-db-host
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

## Notes
For beta simplicity:
- `QUEUE_CONNECTION=sync` is okay initially
- `CACHE_DRIVER=file` is okay initially
- `SESSION_DRIVER=file` can be okay initially

Later you may improve these.

---

# 10. Production-safe Laravel setup steps

After code is on server:

## Step 1
Install dependencies

```bash
composer install --no-dev --optimize-autoloader
```

## Step 2
Create/copy `.env`

```bash
cp .env.example .env
```

Then edit `.env`.

## Step 3
Generate app key

```bash
php artisan key:generate
```

## Step 4
Run migrations

```bash
php artisan migrate --force
```

## Step 5
Run seeders

```bash
php artisan db:seed --force
```

## Step 6
Create storage symlink

```bash
php artisan storage:link
```

## Step 7
Cache config/routes/views

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

# 11. File permissions checklist

Laravel needs proper write permissions for:

- `storage/`
- `bootstrap/cache/`

If these are not writable, Laravel will fail in ugly ways.

## Confirm writable:
- `storage/logs`
- `storage/framework`
- `bootstrap/cache`

---

# 12. Public entry point check

Your subdomain must resolve to Laravel’s `public/index.php`.

Once deployed, visiting:
- `https://app.yourdomain.com`

should load the Laravel app, not:
- directory listing
- DreamHost placeholder page
- WordPress
- raw PHP error output

---

# 13. HTTPS / SSL

You absolutely want HTTPS.

## In DreamHost:
Enable SSL certificate for:
- `app.yourdomain.com`

This matters because Bulldog handles:
- login
- identity
- minors
- protected data

No beta should run without SSL.

---

# 14. Session/auth considerations

Since this is a web app on a subdomain:

## For beta, start simple
Use standard Laravel session auth.

That is fine for:
- browser use
- desktop
- mobile
- home-screen saved usage

## Important
Make sure cookie/session domain behavior works correctly on the subdomain.

If needed:
- confirm `APP_URL`
- confirm secure cookies under HTTPS

---

# 15. DreamHost-specific practical concerns

## Shared hosting limitations
DreamHost shared hosting can be fine for beta, but watch for:
- long-running tasks
- queue workers
- websocket-style features
- high concurrency
- cron/worker limitations

For Phase 1 + 2, that is usually okay.

## For now
You are not yet building:
- heavy real-time systems
- advanced background analytics
- messaging
- live multi-editor game sync

So this is acceptable for beta.

---

# 16. Deployment workflow recommendation

For beta, keep deployments simple and repeatable.

## Suggested workflow
1. develop locally
2. commit changes
3. deploy updated files to server
4. run migrations if needed
5. clear/rebuild caches
6. smoke test key flows

## Useful commands
```bash
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

# 17. Basic smoke test after deployment

After each deployment, verify:

## Public
- app subdomain loads
- SSL works

## Auth
- register works
- login works
- logout works

## Core data
- sports are seeded
- create team works
- team list works
- add roster entry works

## Permission behavior
- unauthorized role assignment blocked
- unauthorized roster edit blocked

---

# 18. Home-screen app readiness checklist

Since you want the same app to behave like a phone-accessible app:

## Initial beta readiness
- responsive layout works on mobile Safari/Chrome
- no broken auth redirects on mobile
- touch targets large enough
- login persistent enough for practical use

## Soon after
Add:
- `manifest.json`
- app icons
- Apple touch icon
- theme color meta tags

That makes save-to-home-screen feel better.

---

# 19. Backups and rollback

Even in beta, do this.

## Minimum backup discipline
Before major migrations:
- export database backup
- keep previous deployment copy if possible

## Why
You are dealing with:
- identity relationships
- team data
- roster history

You want rollback ability if something breaks.

---

# 20. Security checklist

## Must-do
- `APP_DEBUG=false` in production
- SSL enabled
- strong DB password
- no secrets committed to repo
- `.env` not publicly accessible
- Laravel project not publicly rooted above `/public`
- authorization enforced server-side

## Do not do
- expose entire project tree publicly
- leave debug stack traces on in production
- mix Bulldog app files into WordPress install

---

# 21. Postgres portability checklist during deployment

Even though beta is on MySQL, during deployment/code review ensure:

- no DB-native enums required
- no MySQL-only triggers/procedures
- no MySQL-specific raw SQL in critical paths
- business logic remains in PHP actions/services
- schema changes stay in migrations
- JSON fields are limited and intentional
- UUIDs are consistent

---

# 22. Recommended deployment milestone sequence

## Milestone 1
Subdomain created and SSL enabled

## Milestone 2
Laravel app skeleton deployed successfully

## Milestone 3
Database connection working

## Milestone 4
Migrations run successfully

## Milestone 5
Sports seeder working

## Milestone 6
Auth flow working

## Milestone 7
Team creation and roster flow working

That is your true beta deployment path.

---

# 23. What I recommend you gather next

Before deployment, collect these:

## DreamHost info
- subdomain name
- document root setting capability
- PHP version
- SSH access confirmation
- MySQL hostname / db / user
- SSL status

## App info
- Laravel version
- whether Breeze/Fortify/Sanctum chosen
- local environment working status

---

# 24. Best next artifact

Now that we have:
- product blueprint
- technical blueprint
- backlog
- schema
- API plan
- Laravel starter architecture
- Codex prompt pack
- DreamHost deployment checklist

the next most useful deliverable is likely:

## Option A
**Laravel file stubs for the first critical files**

or

## Option B
**DreamHost pre-deployment readiness checklist** in plain owner/operator language

or

## Option C
**A step-by-step “first local Laravel setup” guide**

---

# My recommendation
Since you are about to move from planning into execution, the best next step is:

# **Laravel file stubs for the first critical files**

That would give you:
- first model stubs
- first request stubs
- first action stubs
- first controller stubs
- first route file
- first seeder

If you want, I can generate that next.