#!/bin/bash

echo "🚀 Installing Reliable Email Monitoring System..."

# Run migrations
echo "📦 Running migrations..."
php artisan migrate

# Clear cache
echo "🧹 Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Test the new system
echo "🧪 Testing the new reliable email monitor..."
php artisan emails:reliable-monitor --max-results=5

# Check scheduler
echo "📅 Checking scheduler configuration..."
php artisan schedule:list

echo "✅ Installation complete!"
echo ""
echo "📋 Next steps:"
echo "1. Update your cron job to run every minute:"
echo "   * * * * * cd /home/edlb2bdo7yna/public_html/odc.com && /opt/alt/php83/usr/bin/php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "2. Test the system by sending an email to engineering@orion-contracting.com"
echo ""
echo "3. Check notifications in your application"
echo ""
echo "🔧 The new system includes:"
echo "- ReliableEmailMonitor command (no mutex issues)"
echo "- Enhanced error handling and retry logic"
echo "- Better IMAP connection management"
echo "- Webhook support for real-time notifications"
echo "- Improved logging and debugging"
