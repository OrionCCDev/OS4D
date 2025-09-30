# cPanel Email Reply Tracking Setup Guide

This guide will help you configure your cPanel hosting to enable email reply tracking for your ODes application.

## 📋 Prerequisites

- Access to cPanel hosting account
- Domain with email hosting enabled
- PHP 8.0+ with Laravel application installed
- SSL certificate (recommended for webhooks)

## 🔧 Step 1: Email Account Setup

### 1.1 Create Email Account for Tracking

1. **Login to cPanel**
2. **Go to Email Accounts**
3. **Create New Email Account:**
   - Email: `designers@yourdomain.com` (or your domain)
   - Password: Create a strong password
   - Mailbox Quota: Set appropriate limit (e.g., 1GB)

### 1.2 Configure Email Forwarding (Recommended)

1. **Go to Email Forwarding**
2. **Add Forwarder:**
   - Forward: `designers@yourdomain.com`
   - Forward to: `your-main-email@yourdomain.com`
   - Keep a copy: ✅ (checked)

## 🔗 Step 2: Webhook Configuration

### 2.1 Set Up Webhook Endpoint

Your application already has webhook endpoints configured:
- **Primary Webhook:** `https://yourdomain.com/email/webhook/incoming`
- **Alternative Webhook:** `https://yourdomain.com/webhook/email/incoming`

### 2.2 Test Webhook Accessibility

1. **Test the webhook URL:**
   ```bash
   curl -X POST https://yourdomain.com/email/webhook/incoming \
        -H "Content-Type: application/json" \
        -d '{"test": "webhook"}'
   ```

2. **Expected Response:**
   ```json
   {
     "status": "success",
     "message": "Reply processed successfully"
   }
   ```

## 📧 Step 3: Email Service Provider Integration

### Option A: Using cPanel's Built-in Email (Basic)

#### 3.1 Enable IMAP Access
1. **Go to Email Accounts**
2. **Click "More" → "Access Webmail"**
3. **Ensure IMAP is enabled**
4. **Note IMAP settings:**
   - Server: `mail.yourdomain.com`
   - Port: `993` (SSL) or `143` (non-SSL)
   - Username: `designers@yourdomain.com`

#### 3.2 Configure Email Forwarding Rules
1. **Go to Email Filters**
2. **Create Filter:**
   - Filter Name: `Email Reply Tracking`
   - Rules:
     - `To` contains `designers@yourdomain.com`
     - `Subject` contains `Re:`
   - Actions:
     - Forward to webhook URL
     - Keep a copy

### Option B: Using External Email Service (Recommended)

#### 3.1 SendGrid Setup
1. **Sign up for SendGrid** (Free tier: 100 emails/day)
2. **Verify your domain**
3. **Configure Inbound Parse:**
   - Go to Settings → Mail Settings → Inbound Parse
   - Add hostname: `inbound.yourdomain.com`
   - POST URL: `https://yourdomain.com/email/webhook/incoming`
4. **Update DNS records** (MX record to SendGrid)

#### 3.2 Mailgun Setup
1. **Sign up for Mailgun** (Free tier: 5,000 emails/month)
2. **Add your domain**
3. **Configure DNS records**
4. **Set up webhook:**
   - Go to Webhooks
   - Add webhook for `message_received` event
   - URL: `https://yourdomain.com/email/webhook/incoming`

## 🔄 Step 4: Automated Email Monitoring

### 4.1 Set Up Cron Job

1. **Go to cPanel → Cron Jobs**
2. **Add New Cron Job:**
   - Minute: `*/5` (every 5 minutes)
   - Hour: `*`
   - Day: `*`
   - Month: `*`
   - Weekday: `*`
   - Command: `cd /home/yourusername/public_html && php artisan email:monitor-replies`

### 4.2 Alternative: Laravel Scheduler

Add to your `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Check for email replies every 5 minutes
    $schedule->command('email:monitor-replies')
             ->everyFiveMinutes()
             ->withoutOverlapping()
             ->runInBackground();
    
    // Check simple email replies every 10 minutes
    $schedule->command('email:check-simple-replies')
             ->everyTenMinutes()
             ->withoutOverlapping()
             ->runInBackground();
}
```

## 🛠️ Step 5: Application Configuration

### 5.1 Environment Variables

Add to your `.env` file:
```env
# Email Reply Tracking
EMAIL_REPLY_WEBHOOK_URL=https://yourdomain.com/email/webhook/incoming
EMAIL_MONITORING_ENABLED=true
EMAIL_REPLY_NOTIFICATIONS=true

# If using external email service
SENDGRID_API_KEY=your_sendgrid_api_key
MAILGUN_DOMAIN=your_mailgun_domain
MAILGUN_SECRET=your_mailgun_secret
```

### 5.2 Database Migration

Run the migration to ensure all email tracking tables are created:
```bash
php artisan migrate
```

## 🧪 Step 6: Testing

### 6.1 Test Email Sending
1. **Send a test email** through your application
2. **Verify** `designers@yourdomain.com` is CC'd
3. **Check database** for tracking record:
   ```sql
   SELECT * FROM emails WHERE email_type = 'sent' ORDER BY created_at DESC LIMIT 1;
   ```

### 6.2 Test Reply Detection
1. **Reply to the sent email** from external email client
2. **Check webhook logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
3. **Verify notification creation:**
   ```sql
   SELECT * FROM email_notifications WHERE notification_type = 'reply_received';
   ```

### 6.3 Manual Testing Commands

```bash
# Test email monitoring
php artisan email:monitor-replies

# Test simple email replies
php artisan email:check-simple-replies

# Test webhook endpoint
php artisan email:monitor-replies --provider=sendgrid
```

## 📊 Step 7: Monitoring Dashboard

### 7.1 Access Monitoring Dashboard
- **URL:** `https://yourdomain.com/email-monitoring`
- **Features:**
  - Email statistics
  - Recent replies
  - Notification management
  - Provider setup instructions

### 7.2 API Endpoints
- `GET /email-monitoring/stats` - Get monitoring statistics
- `POST /email-monitoring/trigger` - Manually trigger monitoring
- `GET /email-monitoring/notifications` - Get user notifications
- `POST /email-monitoring/notifications/{id}/mark-read` - Mark notification as read

## 🔧 Troubleshooting

### Common Issues

#### 1. Webhook Not Receiving Emails
- **Check:** Webhook URL accessibility
- **Verify:** DNS records are correct
- **Test:** Use curl to test webhook endpoint

#### 2. Emails Not Being Tracked
- **Check:** `designers@yourdomain.com` is CC'd in sent emails
- **Verify:** Email service configuration
- **Check:** Database for email records

#### 3. Notifications Not Sent
- **Check:** Laravel queue is running
- **Verify:** Email configuration in `.env`
- **Check:** Notification preferences

#### 4. Cron Jobs Not Running
- **Check:** Cron job syntax in cPanel
- **Verify:** File paths are correct
- **Test:** Run command manually

### Debug Commands

```bash
# Check email tracking status
php artisan email:monitor-replies

# View recent logs
tail -f storage/logs/laravel.log

# Check database records
php artisan tinker
>>> App\Models\Email::where('email_type', 'sent')->count()
>>> App\Models\EmailNotification::where('notification_type', 'reply_received')->count()
```

## 📈 Advanced Configuration

### Email Service Provider Setup

For detailed setup instructions for specific providers:

```bash
# Get setup instructions for SendGrid
php artisan email:monitor-replies --provider=sendgrid

# Get setup instructions for Mailgun
php artisan email:monitor-replies --provider=mailgun

# Get setup instructions for Postmark
php artisan email:monitor-replies --provider=postmark
```

### Custom Email Forwarding

If you need custom email forwarding rules:

1. **Go to Email Filters in cPanel**
2. **Create advanced filter:**
   - Match: `To` contains `designers@yourdomain.com`
   - AND `Subject` contains `Re:`
   - Action: Forward to webhook URL

## 🎯 Next Steps

1. **Complete the cPanel setup** following this guide
2. **Test the email reply tracking** functionality
3. **Monitor the system** using the dashboard
4. **Set up notifications** for your team
5. **Configure external stakeholders** if needed

## 📞 Support

If you encounter issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Test webhook endpoints manually
3. Verify email service provider configuration
4. Check database for email records

---

**Note:** This setup enables comprehensive email reply tracking with notifications. The system will automatically detect replies to emails sent through your application and notify relevant users.
