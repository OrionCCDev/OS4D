# 🚀 Laravel Broadcasting Deployment Guide

## Real-time Notification System Migration from AJAX to WebSockets

This guide will walk you through deploying the new real-time notification system using Laravel Broadcasting with Redis and WebSockets.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Installation Steps](#installation-steps)
4. [Configuration](#configuration)
5. [Testing](#testing)
6. [Production Deployment](#production-deployment)
7. [Troubleshooting](#troubleshooting)
8. [Rollback Plan](#rollback-plan)

---

## 🎯 Overview

### What Changed?

**Before (AJAX Polling):**
- Notifications checked every 30 seconds
- 50 users = 100 HTTP requests/minute
- 0-30 second latency
- High server load

**After (WebSocket Broadcasting):**
- Real-time push notifications
- 50 users = ~0 polling requests
- <100ms latency
- 95% less server load

### Architecture

```
User Browser → Laravel Echo (WebSocket) → Laravel WebSockets Server → Redis → Laravel App
```

---

## ✅ Prerequisites

### Required Software

1. **Redis Server**
   - Version: 6.0 or higher
   - Used for: Broadcasting, caching, queues

2. **PHP Redis Extension**
   - Required for PHP to communicate with Redis
   - Package: `php-redis` or `phpredis`

3. **Node.js & NPM**
   - Version: Node 18+ recommended
   - Used for: Building frontend assets

4. **Composer**
   - For installing PHP dependencies

### Check Your Environment

Run these commands in your **cPanel Terminal** or SSH:

```bash
# Check Redis
redis-cli ping
# Expected output: PONG

# Check PHP Redis extension
php -m | grep redis
# Expected output: redis

# Check PHP version
php -v
# Expected output: PHP 8.2.x

# Check Node version
node -v
# Expected output: v18.x.x or higher
```

---

## 📦 Installation Steps

### Step 1: Install Redis (If Not Already Installed)

**On cPanel/WHM:**
1. Go to EasyApache 4
2. Search for "Redis"
3. Install `ea-redis` and `ea-phpXX-php-redis` (where XX is your PHP version)

**Or via Terminal:**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server

# Verify installation
redis-cli ping
```

### Step 2: Install PHP Dependencies

In your application directory via cPanel Terminal:

```bash
cd /home/yourusername/public_html

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# OR if composer is not in PATH
php composer.phar install --no-dev --optimize-autoloader
```

**Expected packages to be installed:**
- `beyondcode/laravel-websockets` (^1.14)
- `predis/predis` (^2.2)

### Step 3: Install Frontend Dependencies

```bash
# Install NPM packages (laravel-echo and pusher-js are already in package.json)
npm install

# Build production assets
npm run build
```

### Step 4: Publish WebSocket Configuration

```bash
# Publish Laravel WebSockets configuration and migrations
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"

# Run migrations
php artisan migrate

# Publish config
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"
```

---

## ⚙️ Configuration

### Step 5: Update .env File

Copy the contents from `.env.broadcasting.example` and add to your `.env` file:

```env
# Broadcasting Configuration
BROADCAST_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Redis Configuration
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_QUEUE_DB=2

# Pusher/WebSocket Configuration
PUSHER_APP_ID=12345
PUSHER_APP_KEY=your-unique-app-key-here
PUSHER_APP_SECRET=your-unique-secret-here
PUSHER_APP_CLUSTER=mt1

# Vite Configuration
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
VITE_PUSHER_HOST="${APP_URL}"
VITE_PUSHER_PORT=6001
VITE_PUSHER_SCHEME=http
```

**Important:**
- Replace `your-unique-app-key-here` with a random string (e.g., use `openssl rand -hex 16`)
- Replace `your-unique-secret-here` with another random string
- For production with SSL, change `VITE_PUSHER_SCHEME` to `https`

### Step 6: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 7: Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🧪 Testing

### Step 8: Test WebSocket Server (Development)

Start the WebSocket server temporarily:

```bash
php artisan websockets:serve
```

**Expected output:**
```
Starting the WebSocket server on port 6001...
```

Keep this terminal open and open a new terminal for the next steps.

### Step 9: Start Queue Worker (Temporary)

In a new terminal:

```bash
php artisan queue:work redis
```

### Step 10: Test in Browser

1. **Open your application** in a browser
2. **Login** to your account
3. **Open browser console** (F12 → Console tab)

Look for these messages:
```
[Laravel Echo] Initialized with host: your-domain.com
[Laravel Echo] ✓ Connected to WebSocket server
[Notifications] Initializing real-time notifications for user: 1
[Notifications] ✓ Subscribed to notification channel
```

### Step 11: Send Test Notification

In another browser tab, visit:
```
https://your-domain.com/test-notification
```

You should see:
- A notification appear in real-time (no page refresh needed)
- Browser notification popup (if permissions granted)
- Toast notification in the corner
- Updated notification count badge

### Step 12: Multi-User Test

1. Open your app in **two different browsers** (or one incognito)
2. Login as **User A** in Browser 1
3. Login as **User B** in Browser 2
4. Create a task/email that notifies User A
5. **Verify User A receives real-time notification**
6. **Verify User B does NOT receive User A's notification**

---

## 🌐 Production Deployment

### Step 13: Setup Supervisor (For Production)

Supervisor keeps the WebSocket server and queue workers running permanently.

**Install Supervisor:**
```bash
sudo apt install supervisor
```

**Create WebSocket Supervisor Config:**

Create file: `/etc/supervisor/conf.d/laravel-websockets.conf`

```ini
[program:laravel-websockets]
process_name=%(program_name)s_%(process_num)02d
command=php /home/yourusername/public_html/artisan websockets:serve
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=yourusername
numprocs=1
redirect_stderr=true
stdout_logfile=/home/yourusername/public_html/storage/logs/websockets.log
stopwaitsecs=3600
```

**Create Queue Worker Supervisor Config:**

Create file: `/etc/supervisor/conf.d/laravel-queue.conf`

```ini
[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /home/yourusername/public_html/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=yourusername
numprocs=4
redirect_stderr=true
stdout_logfile=/home/yourusername/public_html/storage/logs/queue-worker.log
stopwaitsecs=3600
```

**Start Supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
sudo supervisorctl status
```

**Expected output:**
```
laravel-queue:laravel-queue_00      RUNNING   pid 12345, uptime 0:00:05
laravel-queue:laravel-queue_01      RUNNING   pid 12346, uptime 0:00:05
laravel-queue:laravel-queue_02      RUNNING   pid 12347, uptime 0:00:05
laravel-queue:laravel-queue_03      RUNNING   pid 12348, uptime 0:00:05
laravel-websockets:websockets_00    RUNNING   pid 12349, uptime 0:00:05
```

### Step 14: Configure Nginx (For WebSocket Proxying)

Add this to your Nginx server block:

```nginx
location /app/ {
    proxy_pass http://127.0.0.1:6001;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

**Restart Nginx:**
```bash
sudo systemctl restart nginx
```

### Step 15: Configure SSL (If Using HTTPS)

Update `.env`:
```env
VITE_PUSHER_SCHEME=https
```

Rebuild assets:
```bash
npm run build
```

---

## 🔍 Monitoring & Debugging

### WebSocket Dashboard

Access the built-in monitoring dashboard:
```
https://your-domain.com/laravel-websockets
```

Features:
- View active connections
- Monitor message statistics
- Debug connection issues
- View app statistics

### Log Files

**WebSocket logs:**
```bash
tail -f storage/logs/websockets.log
```

**Queue worker logs:**
```bash
tail -f storage/logs/queue-worker.log
```

**Laravel logs:**
```bash
tail -f storage/logs/laravel.log
```

### Debug Console

In browser console, run:

```javascript
// Check connection status
window.notificationDebug.getConnectionStatus()

// Get current channel
window.notificationDebug.getChannel()

// Test notification manually
window.notificationDebug.testNotification()

// Force AJAX fallback
window.notificationDebug.forceAjaxFallback()

// Fetch counts manually
window.notificationDebug.fetchCounts()
```

---

## 🛠️ Troubleshooting

### Issue: "Class 'Redis' not found"

**Solution:**
```bash
# Install PHP Redis extension
sudo apt install php8.2-redis
# OR
sudo pecl install redis

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### Issue: "Connection refused to localhost:6001"

**Solutions:**
1. Check if WebSocket server is running:
   ```bash
   sudo supervisorctl status laravel-websockets
   ```

2. Check if port 6001 is open:
   ```bash
   sudo netstat -tulpn | grep 6001
   ```

3. Check firewall:
   ```bash
   sudo ufw allow 6001
   ```

### Issue: "Redis connection failed"

**Solutions:**
1. Check Redis status:
   ```bash
   sudo systemctl status redis-server
   ```

2. Start Redis:
   ```bash
   sudo systemctl start redis-server
   ```

3. Test Redis connection:
   ```bash
   redis-cli ping
   ```

### Issue: Notifications work but no sound

**Solution:**
- Browser must have user interaction before playing audio
- Check browser console for audio play errors
- Grant audio permissions in browser settings

### Issue: Browser notifications not showing

**Solution:**
1. Check notification permissions in browser
2. Request permission:
   ```javascript
   Notification.requestPermission()
   ```

### Issue: WebSocket connects but no notifications received

**Solutions:**
1. Check queue worker is running:
   ```bash
   sudo supervisorctl status laravel-queue
   ```

2. Check if events are being broadcasted:
   ```bash
   tail -f storage/logs/laravel.log | grep "broadcasted"
   ```

3. Verify user ID meta tag exists:
   ```javascript
   document.querySelector('meta[name="user-id"]').content
   ```

---

## 🔄 Rollback Plan

If you need to rollback to AJAX polling:

### Option 1: Disable Broadcasting (Keep code, use AJAX)

**Update .env:**
```env
BROADCAST_DRIVER=log
```

**Clear config:**
```bash
php artisan config:clear
```

The system will automatically fallback to AJAX polling.

### Option 2: Remove Broadcasting Code (Complete Rollback)

**Revert to previous commit:**
```bash
git revert HEAD
git push
```

**Or checkout previous branch:**
```bash
git checkout main
```

---

## 📊 Performance Monitoring

### Expected Metrics

**With 50 concurrent users:**

| Metric | Before (AJAX) | After (WebSocket) | Improvement |
|--------|---------------|-------------------|-------------|
| HTTP Requests/min | 100 | ~5 | 95% ↓ |
| Average Latency | 0-30s | <100ms | 99.7% ↓ |
| DB Queries/min | 100 | ~10 | 90% ↓ |
| Server CPU | Medium | Low | ~50% ↓ |
| User Experience | Delayed | Instant | ∞ ↑ |

### Monitoring Commands

```bash
# Check Redis memory usage
redis-cli info memory

# Check queue size
php artisan queue:monitor

# Check WebSocket connections
curl http://localhost:6001/apps

# Monitor supervisor processes
sudo supervisorctl tail -f laravel-websockets stdout
sudo supervisorctl tail -f laravel-queue stdout
```

---

## 📝 Maintenance

### Daily Tasks

1. Monitor logs for errors
2. Check supervisor status
3. Verify Redis memory usage

### Weekly Tasks

1. Review WebSocket dashboard statistics
2. Check queue failed jobs
3. Clear old logs

### Monthly Tasks

1. Update dependencies
2. Review and optimize Redis configuration
3. Performance testing

---

## 🆘 Support

### Common Commands

```bash
# Restart everything
sudo supervisorctl restart all
php artisan queue:restart
php artisan config:clear

# View all supervisor logs
sudo supervisorctl tail -f laravel-websockets stdout
sudo supervisorctl tail -f laravel-queue stdout

# Check Laravel logs
tail -f storage/logs/laravel.log

# Monitor Redis
redis-cli monitor
```

### Need Help?

1. Check logs first (WebSocket, queue, Laravel)
2. Check `/laravel-websockets` dashboard
3. Test with `/test-notification` route
4. Review this documentation
5. Check Laravel Broadcasting docs: https://laravel.com/docs/broadcasting

---

## ✅ Deployment Checklist

- [ ] Redis installed and running
- [ ] PHP Redis extension installed
- [ ] Composer dependencies installed
- [ ] NPM dependencies installed
- [ ] Assets built (npm run build)
- [ ] .env file updated with broadcasting config
- [ ] Migrations run
- [ ] Caches cleared
- [ ] Supervisor configured and running
- [ ] Nginx configured for WebSocket proxy
- [ ] SSL configured (if using HTTPS)
- [ ] Test notifications working
- [ ] Multi-user test passed
- [ ] Monitoring dashboard accessible
- [ ] Logs are being written correctly
- [ ] Fallback to AJAX tested
- [ ] Production environment variables set

---

## 🎉 Success Indicators

Your deployment is successful if:

✅ No errors in browser console
✅ WebSocket shows "connected" status
✅ Test notifications appear instantly
✅ Notification counts update in real-time
✅ Sounds play on new notifications
✅ Browser notifications work
✅ Multiple users receive their own notifications only
✅ Fallback to AJAX works if WebSocket disconnects
✅ Supervisor processes stay running
✅ No errors in logs

---

**Congratulations! Your real-time notification system is now live! 🚀**
