# cPanel Email Setup Guide for ODes Task Management

## 📧 Setting Up cPanel Email for Sending

### **Step 1: Create Email Account in cPanel**

1. **Login to cPanel**
2. **Go to Email Accounts**
3. **Create New Email Account:**
   - Email: `noreply@yourdomain.com` (or any email you prefer)
   - Password: Create a strong password
   - Mailbox Quota: Set appropriate limit (e.g., 1GB)

### **Step 2: Configure SMTP Settings**

**Get SMTP Information from cPanel:**
- **SMTP Host**: Usually `mail.yourdomain.com` or your server's hostname
- **SMTP Port**: 
  - `587` (TLS/STARTTLS) - Recommended
  - `465` (SSL) - Alternative
  - `25` (Plain) - Not recommended for security
- **Username**: Your full email address
- **Password**: The password you set for the email account

### **Step 3: Update Laravel Configuration**

**Update your `.env` file:**
```env
# Mail Configuration for cPanel
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="ODes Task Management"

# IMAP Configuration for receiving emails
MAIL_IMAP_HOST=mail.yourdomain.com
MAIL_IMAP_PORT=993
MAIL_IMAP_USERNAME=noreply@yourdomain.com
MAIL_IMAP_PASSWORD=your-email-password
MAIL_IMAP_ENCRYPTION=ssl
```

### **Step 4: Test Email Sending**

```bash
# Test email configuration
php artisan tinker
>>> Mail::raw('Test email from cPanel', function($message) {
    $message->to('test@example.com')->subject('Test from ODes');
});
```

## 📬 Setting Up Email Tracking and Notifications

### **Method 1: Email Forwarding + Webhook (Recommended)**

**Step 1: Set up Email Forwarding in cPanel**
1. Go to **Email Forwarders** in cPanel
2. Create a forwarder: `noreply@yourdomain.com` → Forward to your webhook URL
3. Or use **Email Routing** to forward to your application

**Step 2: Configure Webhook Endpoint**
Your application now has a webhook endpoint at:
```
POST /webhook/email/incoming
```

**Step 3: Set up Email Service Provider (Optional but Recommended)**
For better email handling, consider using services like:
- **Mailgun** (Free tier: 5,000 emails/month)
- **SendGrid** (Free tier: 100 emails/day)
- **Postmark** (Free tier: 100 emails/month)

These services provide webhook support for incoming emails.

### **Method 2: IMAP Integration**

**Step 1: Enable IMAP in cPanel**
1. Go to **Email Accounts**
2. Click **More** → **Access Webmail**
3. Ensure IMAP is enabled

**Step 2: Set up Scheduled Email Checking**
Create a scheduled task to check for new emails:

```bash
# Add to crontab
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

**Step 3: Add to Laravel Schedule**
Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Check for new emails every 5 minutes
    $schedule->call(function () {
        app(EmailController::class)->checkNewEmails();
    })->everyFiveMinutes();
}
```

### **Method 3: Email Service Provider Integration**

**Using Mailgun (Recommended):**

1. **Sign up for Mailgun**
2. **Add your domain**
3. **Configure DNS records**
4. **Set up webhook endpoint**

**Update `.env`:**
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-secret
```

**Webhook URL:** `https://yourdomain.com/webhook/email/incoming`

## 🔔 Notification System

### **Email Notifications Features**

The system now includes:

1. **Real-time Notifications**
   - Database notifications
   - Email notifications
   - In-app notifications

2. **Smart Email Matching**
   - Automatically links emails to tasks
   - Matches by task ID in subject
   - Matches by task title

3. **User Notifications**
   - Task assignees get notified
   - Managers get notified
   - Relevant users get notified

### **Notification Types**

1. **Email Received Notification**
   - Sent when new email arrives
   - Includes email preview
   - Links to related task

2. **Task-Related Email Notification**
   - Sent when email relates to specific task
   - Includes task context
   - Direct link to task

## 🛠️ Implementation Steps

### **Step 1: Run Migrations**
```bash
php artisan migrate
```

### **Step 2: Set up Email Views**
Create email management views:

```bash
# Create email views directory
mkdir -p resources/views/emails
```

### **Step 3: Configure Notifications**
The notification system is already set up and will:
- Store notifications in database
- Send email notifications
- Display in-app notifications

### **Step 4: Test the System**

1. **Send a test email** to your cPanel email
2. **Check webhook endpoint** receives the email
3. **Verify notifications** are sent to users
4. **Check email storage** in database

## 📊 Email Management Features

### **Email Dashboard**
- View all received emails
- Filter by status (received, read, replied, archived)
- Search emails by subject or sender
- Link emails to tasks

### **Email Tracking**
- Track email status
- Monitor reply chains
- Archive old emails
- Export email data

### **Task Integration**
- Automatically link emails to tasks
- Show email history for tasks
- Send notifications for task-related emails

## 🔧 Troubleshooting

### **Common Issues**

1. **SMTP Authentication Failed**
   - Check username/password
   - Verify SMTP settings
   - Check firewall settings

2. **Emails Not Received**
   - Check webhook endpoint
   - Verify email forwarding
   - Check spam folder

3. **Notifications Not Working**
   - Check notification settings
   - Verify user preferences
   - Check email configuration

### **Debug Commands**

```bash
# Check email configuration
php artisan tinker
>>> config('mail')

# Test email sending
>>> Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });

# Check IMAP connection
>>> imap_open('{mail.yourdomain.com:993/imap/ssl}', 'email@domain.com', 'password')
```

## 🚀 Advanced Features

### **Email Templates**
- Custom email templates
- Dynamic content
- Branded emails

### **Email Analytics**
- Track email open rates
- Monitor reply rates
- Generate email reports

### **Bulk Email Operations**
- Send bulk emails
- Email campaigns
- Scheduled emails

### **Email Security**
- SPF records
- DKIM signatures
- DMARC policies

## 📈 Monitoring and Maintenance

### **Email Logs**
- Monitor email sending
- Track delivery status
- Log errors and issues

### **Performance Optimization**
- Queue email sending
- Optimize database queries
- Cache email data

### **Backup and Recovery**
- Backup email data
- Restore email history
- Export email archives

## 🎯 Best Practices

### **Email Management**
- Regular cleanup of old emails
- Archive important emails
- Monitor email quotas

### **Security**
- Use strong passwords
- Enable 2FA for email accounts
- Regular security updates

### **Performance**
- Monitor email server performance
- Optimize email queries
- Use email caching

This setup provides a complete email management system with tracking, notifications, and integration with your task management system.
