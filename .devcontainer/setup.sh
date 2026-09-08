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