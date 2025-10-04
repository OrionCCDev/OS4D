# AUTOMATIC EMAIL DETECTION - CRON JOBS SETUP

## Current Cron Jobs (Based on Your cPanel)

You already have these cron jobs set up:

### 1. Laravel Scheduler (Every Day at Midnight)
```
0 0 * * * cd /public_html/issue-tracker.com && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Email Monitor Replies (Every 5 Minutes)
```
*/5 * * * * /usr/local/bin/php /home/ed1b2bdo7yna/public_html/odc.com/artisan email:monitor-replies
```

## Recommended Additional Cron Jobs

Add these cron jobs to your cPanel for complete automatic email detection:

### 3. Automatic Email Detection (Every 2 Minutes)
```
*/2 * * * * /usr/local/bin/php /home/ed1b2bdo7yna/public_html/odc.com/artisan email:detect-sent
```

### 4. Live Email Monitoring (Every 2 Minutes)
```
*/2 * * * * /usr/local/bin/php /home/ed1b2bdo7yna/public_html/odc.com/artisan email:live-monitor
```

### 5. Designers Inbox Monitoring (Every 5 Minutes)
```
*/5 * * * * /usr/local/bin/php /home/ed1b2bdo7yna/public_html/odc.com/artisan email:monitor-designers-inbox
```

## Complete Cron Job List

Here are ALL the cron jobs you should have for complete automation:

| Minute | Hour | Day | Month | Weekday | Command |
|--------|------|-----|-------|---------|---------|
| 0 | 0 | * | * | * | `cd /public_html/issue-tracker.com && php artisan schedule:run >> /dev/null 2>&1` |
| */5 | * | * | * | * | `/usr/local/bin/php /home/ed1b2bdo7yna/public_html/odc.com/artisan email:monitor-replies` |
| */2 | * | * | * | * | `/usr/local/bin/php /home/ed1b2bdo7yna/public_html/odc.com/artisan email:detect-sent` |
| */2 | * | * | * | * | `/usr/local/bin/php /home/ed1b2bdo7yna/public_html/odc.com/artisan email:live-monitor` |
| */5 | * | * | * | * | `/usr/local/bin/php /home/ed1b2bdo7yna/public_html/odc.com/artisan email:monitor-designers-inbox` |

## What Each Cron Job Does

### 1. Laravel Scheduler (Every Day)
- Runs all scheduled Laravel commands
- Handles email reply checking
- Manages email monitoring
- **Status**: ✅ Already configured

### 2. Email Monitor Replies (Every 5 Minutes)
- Checks for email replies
- Processes webhook data
- **Status**: ✅ Already configured

### 3. Automatic Email Detection (Every 2 Minutes) - **ADD THIS**
- Detects sent emails automatically
- Creates notifications for sender and manager
- No manual intervention needed
- **Status**: ❌ Needs to be added

### 4. Live Email Monitoring (Every 2 Minutes) - **ADD THIS**
- Monitors engineering@orion-contracting.com inbox
- Creates live notifications
- Updates statistics
- **Status**: ❌ Needs to be added

### 5. Designers Inbox Monitoring (Every 5 Minutes) - **ADD THIS**
- Monitors engineering@orion-contracting.com specifically
- Processes incoming emails
- Creates notifications for relevant users
- **Status**: ❌ Needs to be added

## How to Add New Cron Jobs

1. **Login to cPanel**
2. **Go to Cron Jobs**
3. **Add New Cron Job** for each missing command
4. **Use the exact commands** shown above
5. **Set the timing** as specified

## Automatic Detection Features

Once all cron jobs are set up, the system will automatically:

### ✅ **Detect Sent Emails**
- Every 2 minutes, check for new sent emails
- Create notifications for sender and manager
- No manual intervention required

### ✅ **Monitor Email Replies**
- Every 5 minutes, check for replies
- Process webhook data automatically
- Create reply notifications

### ✅ **Live Email Monitoring**
- Every 2 minutes, update live statistics
- Monitor engineering@orion-contracting.com inbox
- Real-time notifications

### ✅ **Email Tracker Updates**
- Automatic updates to email tracker page
- Real-time statistics
- Live notifications in header

## Testing the System

After adding the cron jobs:

1. **Send an email** from your app
2. **Wait 2 minutes** for automatic detection
3. **Check Email Tracker** page for the new email
4. **Check notification icons** in header
5. **Manager should see** all emails automatically

## Monitoring Cron Jobs

To check if cron jobs are working:

1. **Check Laravel logs**: `storage/logs/laravel.log`
2. **Look for these messages**:
   - "🔍 Starting automatic email detection..."
   - "✅ Automatic email detection completed: X notifications created"
   - "Found X sent emails without notifications"

## Troubleshooting

If cron jobs aren't working:

1. **Check cron job syntax** in cPanel
2. **Verify file paths** are correct
3. **Check Laravel logs** for errors
4. **Test commands manually** first:
   ```bash
   php artisan email:detect-sent
   php artisan email:live-monitor
   ```

## Summary

- **Current**: 2 cron jobs configured
- **Needed**: 3 additional cron jobs
- **Result**: Complete automatic email detection
- **No manual intervention** required
- **Real-time notifications** for all users
- **Manager oversight** of all email activity

Add the 3 missing cron jobs and your email tracking system will be fully automatic!
