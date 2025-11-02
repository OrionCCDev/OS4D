#!/bin/bash

# Production Deployment Script for Dashboard Fixes
# Run this on your cPanel server

echo "=========================================="
echo "Dashboard Fix Deployment Script"
echo "=========================================="
echo ""

# Get current directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo "1. Pulling latest changes from Git..."
git fetch origin
git checkout claude/fix-manager-dashboard-500-011CUihF1ZeAqR3kSWrjQRfa
git pull origin claude/fix-manager-dashboard-500-011CUihF1ZeAqR3kSWrjQRfa

if [ $? -ne 0 ]; then
    echo "ERROR: Failed to pull from Git"
    exit 1
fi

echo "✓ Git pull successful"
echo ""

echo "2. Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

if [ $? -ne 0 ]; then
    echo "WARNING: Some cache clearing commands failed (this is often OK)"
fi

echo "✓ Caches cleared"
echo ""

echo "3. Verifying route registration..."
php artisan route:list --name=dashboard.top-performers 2>&1 | grep -q "dashboard.top-performers"

if [ $? -eq 0 ]; then
    echo "✓ Route 'dashboard.top-performers' is registered"
else
    echo "WARNING: Route not found - trying to cache routes..."
    php artisan route:cache 2>&1
fi

echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Visit: https://odc.com.orion-contracting.com/dashboard"
echo "2. Test the Top 3 Competition section (Quarter/Year buttons)"
echo "3. If issues persist, run: php test_dashboard_diagnostics.php"
echo ""
echo "To view logs: tail -100 storage/logs/laravel-*.log"
echo ""
