# Email Tracking and CC Setup Guide

## Overview
This guide explains how to set up email tracking and automatic CC functionality for the ODes application. The system will:

1. **Automatically CC `designers@orion-contracting.com`** on all outgoing emails
2. **Track email opens** using tracking pixels
3. **Monitor email replies** using Gmail API
4. **Create notifications** when replies are received
5. **Provide email statistics** (sent, opened, replied rates)

## Features Implemented

### 1. Automatic CC Functionality
- All outgoing emails automatically CC `designers@orion-contracting.com`
- Works with both Gmail OAuth and regular SMTP sending
- CC is added at the service level, so it's transparent to users

### 2. Email Tracking System
- **Tracking Pixels**: Invisible 1x1 pixel images track when emails are opened
- **Gmail Thread Monitoring**: Monitors Gmail threads for replies
- **Database Storage**: All email interactions are stored in the database
- **Real-time Notifications**: Instant notifications when replies are received

### 3. Email Notifications
- **Reply Notifications**: Alert when someone replies to your emails
- **Open Notifications**: Alert when emails are opened
- **Unread Counter**: Track unread notifications
- **Email Statistics**: View open rates, reply rates, etc.

## Database Setup

### Step 1: Run Migrations
```bash
# Make sure your database is running (MySQL/PostgreSQL)
php artisan migrate

# Or if using SQLite
php artisan migrate --database=sqlite
```

### Step 2: Verify Tables Created
The following tables will be created:
- `emails` (updated with tracking fields)
- `email_notifications` (new table for notifications)

## Configuration

### Step 1: Update Gmail OAuth Scopes
Make sure your Gmail OAuth configuration includes the necessary scopes:

```php
// In config/services.php
'gmail' => [
    'client_id' => env('GMAIL_CLIENT_ID'),
    'client_secret' => env('GMAIL_CLIENT_SECRET'),
    'redirect_uri' => env('GMAIL_REDIRECT_URI'),
    'scopes' => [
        'https://www.googleapis.com/auth/gmail.send',
        'https://www.googleapis.com/auth/gmail.readonly',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile'
    ]
]
```

### Step 2: Set Up Scheduled Tasks
Add this to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Check for email replies every 5 minutes
    $schedule->command('email:check-replies')
             ->everyFiveMinutes()
             ->withoutOverlapping();
}
```

### Step 3: Set Up Cron Job
Add this to your server's crontab:
```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## Usage

### 1. Sending Emails
Emails are automatically tracked when sent through the Gmail OAuth service:

```php
// In TaskController or any email sending code
$gmailOAuthService = app(\App\Services\GmailOAuthService::class);
$success = $gmailOAuthService->sendEmail($user, $emailData);
```

The system will automatically:
- Add `designers@orion-contracting.com` to CC
- Generate a tracking pixel
- Store email details in database
- Monitor for replies

### 2. Checking Email Replies
Run the command manually to check for replies:
```bash
php artisan email:check-replies
```

Or check for a specific user:
```bash
php artisan email:check-replies --user=123
```

### 3. Viewing Notifications
- Navigate to `/email-notifications` to view all email notifications
- Use the API endpoints to get unread counts and statistics

### 4. Email Statistics
Access email statistics via:
```bash
# API endpoint
GET /email-notifications/stats

# Returns:
{
    "sent": 50,
    "opened": 35,
    "replied": 12,
    "open_rate": 70.0,
    "reply_rate": 24.0
}
```

## API Endpoints

### Email Notifications
- `GET /email-notifications` - List all notifications
- `POST /email-notifications/{id}/mark-read` - Mark notification as read
- `POST /email-notifications/mark-all-read` - Mark all as read
- `GET /email-notifications/unread-count` - Get unread count
- `GET /email-notifications/stats` - Get email statistics
- `GET /emails/{id}/show` - Show email details

### Tracking Pixel
- `GET /email/track/{messageId}.png` - Tracking pixel (no auth required)

## Files Created/Modified

### New Files
- `app/Services/EmailTrackingService.php` - Main tracking service
- `app/Models/EmailNotification.php` - Notification model
- `app/Http/Controllers/EmailNotificationController.php` - Notification controller
- `app/Console/Commands/CheckEmailReplies.php` - Command to check replies
- `resources/views/notifications/email-notifications.blade.php` - Notifications view
- `resources/views/emails/show.blade.php` - Email details view
- `database/migrations/2025_09_29_104402_add_email_tracking_fields_to_emails_table.php`
- `database/migrations/2025_09_29_104415_create_email_notifications_table.php`

### Modified Files
- `app/Models/Email.php` - Added tracking fields and relationships
- `app/Services/GmailOAuthService.php` - Added CC and tracking integration
- `app/Http/Controllers/TaskController.php` - Added CC to email sending
- `app/Models/Task.php` - Added CC to stakeholder notifications
- `routes/web.php` - Added notification and tracking routes

## Testing

### 1. Test Email Sending
1. Send an email through the application
2. Check that `designers@orion-contracting.com` is CC'd
3. Verify email is stored in database with tracking info

### 2. Test Reply Tracking
1. Reply to a sent email from external email client
2. Run `php artisan email:check-replies`
3. Check that notification is created
4. Verify reply is stored in database

### 3. Test Open Tracking
1. Open the sent email
2. Check that `opened_at` timestamp is updated
3. Verify open notification is created

## Troubleshooting

### Common Issues

1. **Circular Dependency Error**
   - Fixed by resolving services dynamically instead of constructor injection

2. **Database Connection Issues**
   - Make sure your database is running
   - Check `.env` file for correct database credentials

3. **Gmail API Errors**
   - Verify Gmail OAuth is properly configured
   - Check that user has Gmail connected
   - Ensure proper scopes are set

4. **Migration Issues**
   - Some migrations may fail with SQLite due to ENUM support
   - Use MySQL or PostgreSQL for full compatibility

### Debug Commands
```bash
# Check Gmail configuration
php artisan tinker
>>> app(\App\Services\GmailOAuthService::class)->checkConfiguration()

# Test Gmail connection
php artisan tinker
>>> app(\App\Services\GmailOAuthService::class)->testGmailConnection($user)

# Check email statistics
php artisan tinker
>>> app(\App\Services\EmailTrackingService::class)->getEmailStats($user)
```

## Security Considerations

1. **Tracking Pixel**: Uses a 1x1 transparent PNG, no sensitive data exposed
2. **Gmail API**: Uses OAuth2 for secure access
3. **Database**: All email data is stored securely
4. **Notifications**: Only accessible to authenticated users

## Performance Considerations

1. **Scheduled Tasks**: Reply checking runs every 5 minutes to avoid API limits
2. **Database Indexes**: Added indexes for better query performance
3. **Caching**: Consider caching email statistics for better performance
4. **Rate Limiting**: Gmail API has rate limits, monitor usage

## Future Enhancements

1. **Real-time Notifications**: WebSocket integration for instant notifications
2. **Email Templates**: Track which templates perform best
3. **Advanced Analytics**: More detailed email performance metrics
4. **Bulk Operations**: Mass email tracking capabilities
5. **Export Features**: Export email statistics to CSV/Excel
