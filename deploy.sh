#!/bin/bash

# Production Deployment Script
# Run this script on your production server to sync with GitHub

echo "🚀 Starting production deployment..."

# Navigate to application directory
cd /home/edlb2bdo7yna/public_html/odc.com

# Check current status
echo "📊 Current git status:"
git status
echo "📝 Recent commits:"
git log --oneline -5

# Pull latest changes
echo "⬇️ Pulling latest changes from GitHub..."
git fetch origin
git reset --hard origin/main

# Update dependencies
echo "📦 Updating dependencies..."
composer install --no-dev --optimize-autoloader

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
echo "🔨 Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Set permissions
echo "🔐 Setting proper permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Verify deployment
echo "✅ Deployment completed!"
echo "📊 Final status:"
git log --oneline -1
php artisan --version

echo "🎉 Production server is now synced with GitHub!"
