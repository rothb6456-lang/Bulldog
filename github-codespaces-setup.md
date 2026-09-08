# GitHub Codespaces Dev Environment for Bulldog Statbook

This guide contains the exact configurations and scripts needed to spin up a fully pre-configured, zero-install cloud development environment using GitHub Codespaces. 

It automatically provisions:
* **PHP 8.2** and **Composer** (pre-configured) [360]
* **MySQL 8.0** database container [198]
* **Node.js (LTS)** for any frontend asset compiling
* **Laravel Installer** (global CLI)
* Standard VS Code extensions for Laravel and Blade formatting

---

## 🛠️ Step 1: Create the Configuration Files

In the root of your GitHub repository, create a directory named `.devcontainer/` and place the following two files inside it.

### File 1: `.devcontainer/devcontainer.json`
This file instructs GitHub on how to build your virtual machine.

```json
{
  "name": "Bulldog Statbook Dev Environment",
  "image": "mcr.microsoft.com/devcontainers/php:1-8.2-bullseye",
  "features": {
    "ghcr.io/devcontainers/features/mysql:1": {
      "version": "8.0",
      "databaseName": "bulldog_local",
      "databaseUser": "bulldog_user",
      "databasePassword": "bulldog_password"
    },
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
  "forwardPorts": [8000, 3306],
  "portsAttributes": {
    "8000": {
      "label": "Laravel App",
      "onAutoForward": "openPreview"
    }
  }
}
```

---

### File 2: `.devcontainer/setup.sh`
This shell script automatically runs inside your container right after it is created. It installs the Laravel Installer globally and handles initial app setup (copying `.env`, running composer dependencies, running migrations, and running seeders) [394, 413, 417].

```bash
#!/usr/bin/env bash

echo "=================================================="
echo "⚡ Starting Bulldog Statbook Post-Create Setup... ⚡"
echo "=================================================="

# 1. Install Laravel Installer globally
composer global require "laravel/installer" --no-interaction

# 2. If a Laravel app doesn't exist yet, we stop here so you can run 'laravel new'
if [ ! -f "composer.json" ]; then
    echo "💡 No composer.json found. Your environment is ready!"
    echo "👉 Run 'laravel new .' in the terminal to initialize your new Laravel application."
    exit 0
fi

# 3. If an app exists, let's run composer dependencies
echo "📦 Installing composer dependencies..."
composer install --no-interaction

# 4. Copy and configure .env if it doesn't exist
if [ ! -f ".env" ]; then
    echo "📝 Creating local .env file..."
    cp .env.example .env
    
    # Replace DB config in .env to match the Codespaces MySQL service
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
    sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
    sed -i 's/DB_PORT=.*/DB_PORT=3306/' .env
    sed -i 's/DB_DATABASE=.*/DB_DATABASE=bulldog_local/' .env
    sed -i 's/DB_USERNAME=.*/DB_USERNAME=bulldog_user/' .env
    sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=bulldog_password/' .env
    
    # Generate Laravel Application Key
    php artisan key:generate
fi

# 5. Wait briefly for MySQL server container to be fully operational
echo "⏳ Waiting for MySQL database..."
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

---

## 🚀 Step 2: Push & Spin Up Your Codespace

1. **Commit and push** the `.devcontainer/` folder to your main branch on GitHub.
2. Go to your repository page on **GitHub**.
3. Click the green **Code** button, select the **Codespaces** tab, and click **Create codespace on main**.
4. GitHub will build your container (this takes ~2-3 minutes on the first run, but is instant on subsequent launches).
5. Once inside, if you already have the Laravel stubs placed in your repo, the setup script will have automatically run migrations and seeded your local database [394, 417].
6. Run `php artisan serve` to start your application and click the pop-up to preview it in your browser!
