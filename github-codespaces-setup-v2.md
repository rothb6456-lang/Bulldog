# GitHub Codespaces Dev Environment for Bulldog Statbook (v2)

This updated guide offers two robust alternatives to resolve the container registry error (`ghcr.io/devcontainers/features/mysql` permission issue). 

Choose **Option A (SQLite)** for the fastest, most reliable, and 100% bulletproof setup. Choose **Option B (Docker Compose)** if you strictly require a real MySQL database instance running in your cloud sandbox.

---

## 🌟 Option A: The SQLite Path (Highly Recommended)
Since the Bulldog Statbook technical blueprint requires the system to be **Postgres-portable by design**, using SQLite for local/cloud prototyping is highly recommended. SQLite requires no extra database server, has zero download overhead, and is 100% immune to registry permission errors. Eloquent will translate everything to MySQL on your DreamHost staging server seamlessly later.

### 1. New `.devcontainer/devcontainer.json`
Replace your `devcontainer.json` file with this single-container, zero-dependency configuration:

```json
{
  "name": "Bulldog Statbook Dev Environment (SQLite)",
  "image": "mcr.microsoft.com/devcontainers/php:1-8.2-bullseye",
  "features": {
    "ghcr.io/devcontainers/features/node:1": {
      "version": "lts"
    }
  },
  "customizations": {
    "vscode": {
      "settings": {
        "php.suggest.basic": false,
        "editor.formatOnSave": true
      },
      "extensions": [
        "onecentlin.laravel5-snippets",
        "onecentlin.laravel-blade",
        "amiralibayati.laravel-artisan",
        "shufo.vscode-blade-formatter",
        "bmewburn.vscode-intelephense-client"
      ]
    }
  },
  "postCreateCommand": "bash .devcontainer/setup.sh",
  "forwardPorts": [8000],
  "portsAttributes": {
    "8000": {
      "label": "Laravel App",
      "onAutoForward": "openPreview"
    }
  }
}
```

### 2. New `.devcontainer/setup.sh`
Replace your `setup.sh` file with this script which automatically creates and sets up your SQLite database file:

```bash
#!/usr/bin/env bash

echo "=================================================="
echo "⚡ Starting Bulldog Statbook SQLite Setup...     ⚡"
echo "=================================================="

# 1. Install Laravel Installer globally
composer global require "laravel/installer" --no-interaction

# 2. Check for application
if [ ! -f "composer.json" ]; then
    echo "💡 No composer.json found. Your environment is ready!"
    echo "👉 Run 'laravel new .' in the terminal to initialize your new Laravel application."
    exit 0
fi

# 3. Install composer dependencies
echo "📦 Installing composer dependencies..."
composer install --no-interaction

# 4. Copy and configure .env for SQLite
if [ ! -f ".env" ]; then
    echo "📝 Creating local .env file..."
    cp .env.example .env
    
    # Configure SQLite database
    touch database/database.sqlite
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    # Remove existing DB parameter defaults to let SQLite use relative path to database.sqlite
    sed -i 's/DB_HOST=.*/# DB_HOST=/' .env
    sed -i 's/DB_PORT=.*/# DB_PORT=/' .env
    sed -i 's/DB_DATABASE=.*/DB_DATABASE=/' .env
    sed -i 's/DB_USERNAME=.*/# DB_USERNAME=/' .env
    sed -i 's/DB_PASSWORD=.*/# DB_PASSWORD=/' .env
    
    # Generate Laravel Application Key
    php artisan key:generate
fi

# 5. Run Migrations and Seed Sports (Baseball / Softball)
if [ -f "artisan" ]; then
    echo "🗄️ Running migrations and seeding SQLite database..."
    php artisan migrate:fresh --seed
    
    echo "📂 Creating storage symlink..."
    php artisan storage:link
fi

echo "=================================================="
echo "🎉 Bulldog Statbook dev environment is ready!   🎉"
echo "=================================================="
```

---

## 🐋 Option B: The Docker Compose Path (For Local MySQL)
If you strictly want to run MySQL inside your Codespace, we can bypass the unreliable Devcontainer Features registry entirely and use a standard, multi-container Docker Compose configuration. This downloads standard containers directly from Docker Hub rather than `ghcr.io`.

Create three files inside your `.devcontainer/` folder:

### 1. `.devcontainer/devcontainer.json`
Instructs Codespaces to use Docker Compose to spin up your environment.

```json
{
  "name": "Bulldog Statbook Dev Environment (MySQL)",
  "dockerComposeFile": "docker-compose.yml",
  "service": "app",
  "workspaceFolder": "/workspace",
  "customizations": {
    "vscode": {
      "settings": {
        "php.suggest.basic": false,
        "editor.formatOnSave": true
      },
      "extensions": [
        "onecentlin.laravel5-snippets",
        "onecentlin.laravel-blade",
        "amiralibayati.laravel-artisan",
        "shufo.vscode-blade-formatter",
        "bmewburn.vscode-intelephense-client"
      ]
    }
  },
  "postCreateCommand": "bash .devcontainer/setup.sh",
  "forwardPorts": [8000],
  "portsAttributes": {
    "8000": {
      "label": "Laravel App",
      "onAutoForward": "openPreview"
    }
  }
}
```

### 2. `.devcontainer/docker-compose.yml`
Creates both your PHP app container and a dedicated MySQL server container.

```yaml
version: '3.8'

services:
  app:
    image: mcr.microsoft.com/devcontainers/php:1-8.2-bullseye
    volumes:
      - ..:/workspace:cached
    command: sleep infinity
    network_mode: service:db # Shares networks so app can talk to 127.0.0.1 for DB

  db:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: bulldog_local
      MYSQL_USER: bulldog_user
      MYSQL_PASSWORD: bulldog_password
      MYSQL_ALLOW_EMPTY_PASSWORD: 'yes'
    ports:
      - "3306:3306"
```

### 3. `.devcontainer/setup.sh`
Configures the app and waits for the Docker MySQL service to boot.

```bash
#!/usr/bin/env bash

echo "=================================================="
echo "⚡ Starting Bulldog Statbook MySQL-Compose Setup...⚡"
echo "=================================================="

# 1. Install Laravel Installer globally
composer global require "laravel/installer" --no-interaction

# 2. Check for application
if [ ! -f "composer.json" ]; then
    echo "💡 No composer.json found. Your environment is ready!"
    echo "👉 Run 'laravel new .' in the terminal to initialize your new Laravel application."
    exit 0
fi

# 3. Install composer dependencies
echo "📦 Installing composer dependencies..."
composer install --no-interaction

# 4. Copy and configure .env for MySQL container
if [ ! -f ".env" ]; then
    echo "📝 Creating local .env file..."
    cp .env.example .env
    
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
    sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
    sed -i 's/DB_PORT=.*/DB_PORT=3306/' .env
    sed -i 's/DB_DATABASE=.*/DB_DATABASE=bulldog_local/' .env
    sed -i 's/DB_USERNAME=.*/DB_USERNAME=bulldog_user/' .env
    sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=bulldog_password/' .env
    
    # Generate Laravel Application Key
    php artisan key:generate
fi

# 5. Wait for the database container to become ready
echo "⏳ Waiting for MySQL database container..."
until mysqladmin ping -h"127.0.0.1" -ubulldog_user -p"bulldog_password" --silent; do
    sleep 1
done

# 6. Run Migrations and Seed Sports (Baseball / Softball)
if [ -f "artisan" ]; then
    echo "🗄️ Running migrations and seeding database..."
    php artisan migrate:fresh --seed
    
    echo "📂 Creating storage symlink..."
    php artisan storage:link
fi

echo "=================================================="
echo "🎉 Bulldog Statbook dev environment is ready!   🎉"
echo "=================================================="
```
