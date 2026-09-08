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
