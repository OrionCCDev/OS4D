#!/bin/bash
# Quick deployment script for production server
# Run this on your production server

echo "🚀 Deploying Blade syntax fix..."
cd /home/orioncon/public_html/odc.com.orion-contracting.com || exit 1

echo "📥 Pulling latest changes..."
git fetch origin
git checkout claude/fix-tasks-show-blade-syntax-011CUzKBrJSuM83ZeJd3WJUR
git pull origin claude/fix-tasks-show-blade-syntax-011CUzKBrJSuM83ZeJd3WJUR

echo "🧹 Clearing Laravel caches..."
php artisan view:clear
php artisan cache:clear
php artisan config:clear

echo "✅ Verifying fix..."
echo "Lines 64-66 should show properly separated @elseif blocks:"
sed -n '64,66p' resources/views/tasks/show.blade.php

echo ""
echo "✨ Deployment complete!"
echo "🔗 Test here: https://odc.com.orion-contracting.com/tasks/60"
