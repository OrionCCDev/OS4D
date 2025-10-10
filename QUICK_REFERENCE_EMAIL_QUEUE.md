# 🚀 Quick Reference: Email Queue System

## Critical Commands

### Check Queue Status
```bash
php check_queue_status.php
```

### Start Queue Worker (Choose ONE method)

**Method 1: Supervisor (Recommended)**
```bash
sudo supervisorctl start odels-queue-worker:*
sudo supervisorctl status odels-queue-worker:*
```

**Method 2: Manual Start**
```bash
php artisan queue:work database --daemon --tries=3 --timeout=300
```

**Method 3: Cron Job**
```bash
# Add to crontab -e
* * * * * cd /path/to/app && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### Restart Queue Worker
```bash
# If using supervisor
sudo supervisorctl restart odels-queue-worker:*

# OR trigger restart from code
php artisan queue:restart
```

### Check Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Jobs
```bash
# Retry specific job
php artisan queue:retry <job-id>

# Retry all failed jobs
php artisan queue:retry all
```

### Clear Failed Jobs
```bash
php artisan queue:flush
```

### Reset Stuck Emails
```bash
php artisan tinker
>>> \App\Models\TaskEmailPreparation::where('status', 'processing')->where('created_at', '<', now()->subMinutes(10))->update(['status' => 'failed', 'error_message' => 'Reset by admin']);
>>> exit
```

---

## Web Dashboard

**URL:** `/admin/queue-monitor`

**Access:** Managers only

**Features:**
- View queue worker status
- See pending/failed jobs
- Retry or delete failed jobs
- Reset stuck emails
- Email statistics

**Auto-refresh:** Every 30 seconds

---

## Important Files

| File | Purpose | Action |
|------|---------|--------|
| `check_queue_status.php` | Diagnostic script | Run on cPanel |
| `/admin/queue-monitor` | Web dashboard | Monitor via browser |
| `storage/logs/laravel.log` | Error logs | Check for errors |
| `storage/logs/queue-worker.log` | Queue worker logs | Check worker output |

---

## Troubleshooting One-Liners

### Queue worker not running?
```bash
sudo supervisorctl restart odels-queue-worker:*
```

### Too many failed jobs?
```bash
php artisan queue:retry all
```

### Emails stuck?
```bash
php check_queue_status.php
```

### Need to see logs?
```bash
tail -100 storage/logs/laravel.log
```

### Emergency: Process all pending now
```bash
php artisan queue:work --stop-when-empty
```

---

## User Experience Flow

### Before Fix ❌
1. Click "Send Email with Attachment"
2. Wait 30+ seconds on loading page
3. No feedback if it fails
4. Silent failures

### After Fix ✅
1. Click "Send Email with Attachment"
2. Instant message: "Email is being sent in the background"
3. Navigate away immediately
4. Receive notification when done (success or failure)
5. View status in Queue Monitor dashboard

---

## Health Checks

**Green (Healthy):**
- ✅ Queue worker: Running
- ✅ Pending jobs: < 10
- ✅ Failed jobs: < 5
- ✅ Stuck emails: 0

**Yellow (Warning):**
- ⚠️ Pending jobs: 10-50
- ⚠️ Failed jobs: 5-20
- ⚠️ Stuck emails: 1-3
- **Action:** Monitor closely, investigate failures

**Red (Critical):**
- ❌ Queue worker: Not Running
- ❌ Pending jobs: > 50
- ❌ Failed jobs: > 20
- ❌ Stuck emails: > 3
- **Action:** Restart queue worker immediately, reset stuck emails

---

## Emergency Recovery

If emails stop sending completely:

1. **Check queue worker:**
   ```bash
   php check_queue_status.php
   ```

2. **Restart if needed:**
   ```bash
   sudo supervisorctl restart odels-queue-worker:*
   ```

3. **Process pending immediately:**
   ```bash
   php artisan queue:work --stop-when-empty
   ```

4. **Check logs:**
   ```bash
   tail -100 storage/logs/laravel.log
   ```

5. **Reset stuck emails:**
   ```bash
   # Via dashboard: /admin/queue-monitor -> "Reset Stuck Emails"
   # Or via CLI (see above)
   ```

---

## Contact for Support

**When to escalate:**
- Queue worker keeps crashing
- High failure rate (>50%)
- Performance degradation
- Unusual errors in logs

**Information to provide:**
1. Output of `php check_queue_status.php`
2. Last 100 lines of laravel.log
3. Screenshot of queue monitor dashboard
4. Description of user-reported issue

---

## Supervisor Configuration (If Needed)

Create: `/etc/supervisor/conf.d/odels-queue-worker.conf`

```ini
[program:odels-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=your-username
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/app/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Apply changes:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start odels-queue-worker:*
```

---

**Remember:** The queue worker MUST be running for background email processing!

**Quick Test:**
1. Go to queue monitor: `/admin/queue-monitor`
2. Check if "Queue Worker Status" shows green "Running"
3. If red "Not Running", execute restart command above

