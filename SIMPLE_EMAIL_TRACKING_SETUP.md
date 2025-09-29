# Simple Email Tracking Setup Guide

## Overview
This is a simplified email tracking solution that automatically CCs `designers@orion-contracting.com` on every email sent through the application and tracks replies through that inbox. **No Google OAuth verification required!**

## How It Works

### 1. **Automatic CC**
- Every email sent through the application automatically CCs `designers@orion-contracting.com`
- This ensures the team is always copied on communications
- No user configuration required

### 2. **Email Tracking**
- Sent emails are stored in the database with tracking information
- Replies are detected by monitoring the `designers@orion-contracting.com` inbox
- Notifications are created when replies are received

### 3. **Reply Detection**
- Uses webhook integration with email service providers (SendGrid, Mailgun, etc.)
- Or IMAP connection to `designers@orion-contracting.com` inbox
- Or email forwarding rules

## Production Setup Steps

### Step 1: Deploy Code Changes
```bash
# Pull latest code
git pull origin main

# Run migrations (if not already done)
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 2: Configure Email Service
Make sure your `.env` file has proper email configuration:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Your Company Name"
```

### Step 3: Set Up Reply Monitoring

#### Option A: Email Service Webhook (Recommended)
1. **SendGrid Setup:**
   - Go to SendGrid Dashboard → Settings → Mail Settings → Inbound Parse
   - Add webhook URL: `https://yourdomain.com/email/webhook/incoming`
   - Forward emails to `designers@orion-contracting.com`

2. **Mailgun Setup:**
   - Go to Mailgun Dashboard → Routes
   - Create route: `designers@orion-contracting.com` → `https://yourdomain.com/email/webhook/incoming`

3. **Other Providers:**
   - Configure webhook to point to: `https://yourdomain.com/email/webhook/incoming`

#### Option B: Email Forwarding Rules
1. Set up email forwarding rules in your email client
2. Forward replies from `designers@orion-contracting.com` to a webhook endpoint
3. Process forwarded emails to detect replies

#### Option C: IMAP Monitoring (Advanced)
1. Set up IMAP connection to `designers@orion-contracting.com`
2. Periodically check for new emails
3. Process replies automatically

### Step 4: Test the System

#### Test Email Sending:
1. Create a test task
2. Send an email through the application
3. Verify `designers@orion-contracting.com` is CC'd
4. Check database for tracking record:
```sql
SELECT * FROM emails WHERE email_type = 'sent' ORDER BY created_at DESC LIMIT 1;
```

#### Test Reply Detection:
1. Reply to the sent email from external email client
2. Check if webhook receives the reply
3. Verify notification is created:
```sql
SELECT * FROM email_notifications WHERE notification_type = 'reply_received';
```

### Step 5: Set Up Automated Monitoring (Optional)

#### Add to Laravel Scheduler (`app/Console/Kernel.php`):
```php
protected function schedule(Schedule $schedule)
{
    // Check for email replies every 10 minutes
    $schedule->command('email:check-simple-replies')
             ->everyTenMinutes()
             ->withoutOverlapping()
             ->runInBackground();
}
```

#### Set Up Server Cron Job:
```bash
# Edit crontab
crontab -e

# Add this line (replace with your actual path)
* * * * * cd /path/to/your/app && php artisan schedule:run >> /dev/null 2>&1
```

## API Endpoints

### Email Tracking:
- `POST /email/webhook/incoming` - Webhook for incoming replies
- `GET /email/check-replies` - Manual check for replies
- `GET /email-notifications/stats` - Get email statistics
- `GET /emails/sent` - List sent emails
- `GET /emails/{id}/show` - Show email details

### Notifications:
- `GET /email-notifications` - List notifications
- `POST /email-notifications/{id}/mark-read` - Mark as read
- `POST /email-notifications/mark-all-read` - Mark all as read
- `GET /email-notifications/unread-count` - Get unread count

## Testing Commands

```bash
# Test email reply checking
php artisan email:check-simple-replies

# Check if routes are registered
php artisan route:list | grep email

# Test webhook endpoint
curl -X POST https://yourdomain.com/email/webhook/incoming \
  -H "Content-Type: application/json" \
  -d '{"from":"test@example.com","subject":"Re: Test Email","body":"This is a reply"}'
```

## Benefits of This Approach

1. **No Google OAuth Required** - No verification process needed
2. **Simple Setup** - Just configure email service and webhook
3. **Automatic CC** - Team is always copied on communications
4. **Reliable Tracking** - Uses standard email service webhooks
5. **Easy Maintenance** - No complex OAuth token management

## Troubleshooting

### Issue 1: Emails Not Being CC'd
- Check if `designers@orion-contracting.com` is in the CC list
- Verify email sending is working properly

### Issue 2: Replies Not Detected
- Check webhook endpoint is accessible
- Verify email service webhook configuration
- Check logs for webhook errors

### Issue 3: Notifications Not Created
- Check database for email records
- Verify notification creation logic
- Check user permissions

## Monitoring

### Check Email Statistics:
```sql
SELECT 
    COUNT(*) as total_emails,
    COUNT(CASE WHEN replied_at IS NOT NULL THEN 1 END) as replied_emails,
    COUNT(CASE WHEN is_tracked = 1 THEN 1 END) as tracked_emails
FROM emails 
WHERE email_type = 'sent' 
AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### Check Notifications:
```sql
SELECT 
    notification_type,
    COUNT(*) as count,
    COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread
FROM email_notifications 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY notification_type;
```

## Security Considerations

1. **Webhook Security**: Implement webhook signature verification
2. **Rate Limiting**: Add rate limiting to webhook endpoints
3. **Input Validation**: Validate all incoming webhook data
4. **Logging**: Log all webhook activity for debugging

This simple solution provides email tracking without the complexity of Google OAuth verification!
