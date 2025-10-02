# Automatic Email Fetch & Notification System

This document describes the new automatic email fetching and notification system implemented for the designers inbox.

## Overview

The system automatically fetches emails from `designers@orion-contracting.com` every 3 minutes, stores them in the database (preventing duplicates), and creates notifications for managers when new emails or replies are received.

## Features

### ✅ Automatic Email Fetching
- **Frequency**: Every 3 minutes via scheduled command
- **Source**: `designers@orion-contracting.com` IMAP inbox
- **Duplicate Prevention**: Advanced checking using message_id, subject+from+date, and body hash
- **Incremental Fetching**: Only fetches new emails since last fetch

### ✅ Manager Notifications
- **Real-time Badge**: Navigation icon shows unread notification count
- **Dropdown Notifications**: Click the envelope icon to see recent notifications
- **Email Details**: Shows sender, subject, received time, and email preview
- **Reply Detection**: Automatically detects and notifies about email replies

### ✅ Duplicate Prevention
- **Primary Check**: Message ID comparison (most reliable)
- **Secondary Check**: Subject + From + Date within 5-minute window
- **Tertiary Check**: Subject + From + Body hash comparison
- **Reply Handling**: Special lenient checking for reply emails

### ✅ Production Logging
- **Comprehensive Logging**: All operations logged with context
- **Error Tracking**: Detailed error logging for debugging
- **Performance Monitoring**: Fetch operation statistics
- **Lock Mechanism**: Prevents concurrent fetch operations

## Files Created/Modified

### New Services
- `app/Services/AutoEmailFetchService.php` - Main auto-fetch service
- `app/Http/Controllers/AutoEmailController.php` - API endpoints for notifications
- `app/Console/Commands/AutoEmailFetchCommand.php` - Scheduled command

### Modified Files
- `routes/web.php` - Added auto-email routes
- `routes/console.php` - Added scheduled command
- `resources/views/emails/all-emails.blade.php` - Updated fetch button and added auto-fetch
- `resources/views/layouts/header.blade.php` - Added notification dropdown functionality

## API Endpoints

### Auto Email Fetch
```
POST /auto-emails/fetch
```
Automatically fetches and processes new emails.

**Response:**
```json
{
  "success": true,
  "message": "Successfully processed 5 emails, stored 3 new emails, created 3 notifications",
  "data": {
    "fetched": 5,
    "stored": 3,
    "skipped": 2,
    "notifications_created": 3
  }
}
```

### Get Unread Count
```
GET /auto-emails/unread-count
```
Returns unread notification count for navigation badge.

**Response:**
```json
{
  "success": true,
  "count": 5
}
```

### Get Recent Notifications
```
GET /auto-emails/recent-notifications
```
Returns recent notifications for dropdown display.

**Response:**
```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "type": "new_email",
      "title": "New Email in Designers Inbox",
      "message": "New email from client@example.com: Project Update",
      "created_at": "2 minutes ago",
      "is_read": false,
      "email": {
        "id": 123,
        "subject": "Project Update",
        "from_email": "client@example.com",
        "received_at": "Dec 15, 14:30"
      }
    }
  ]
}
```

### Mark Notification as Read
```
POST /auto-emails/notifications/{id}/mark-read
```
Marks a specific notification as read.

### Mark All Notifications as Read
```
POST /auto-emails/notifications/mark-all-read
```
Marks all notifications as read for the current manager.

## Scheduled Commands

### Primary Command
```bash
php artisan emails:auto-fetch --max-results=50
```
- **Schedule**: Every 3 minutes
- **Purpose**: Main auto-fetch with notifications
- **Max Results**: 50 emails per run

### Legacy Command (Still Active)
```bash
php artisan emails:fetch-designers-inbox --max-results=100
```
- **Schedule**: Every 5 minutes
- **Purpose**: Backup fetch system
- **Max Results**: 100 emails per run

## Configuration

### IMAP Settings
The system uses existing IMAP configuration from `config/mail.php`:

```php
'imap' => [
    'host' => 'mail.orion-contracting.com',
    'port' => 993,
    'username' => 'designers@orion-contracting.com',
    'password' => env('IMAP_PASSWORD'),
    'folder' => 'INBOX',
],
```

### Notification Settings
- **Managers Only**: Only users with `role = 'manager'` or `role = 'admin'` receive notifications
- **Auto-refresh**: Navigation badge updates every 30 seconds
- **Sound Alerts**: Notification sound plays for new emails (if configured)

## Testing

### Manual Test
```bash
php artisan emails:auto-fetch --max-results=10
```

### Test Script
```bash
php test_auto_email_fetch.php
```

### Browser Testing
1. Navigate to `/emails-all`
2. Click "Fetch & Store Emails" button
3. Check navigation badge for notification count
4. Click envelope icon to see notifications dropdown

## Monitoring & Logs

### Log Files
- `storage/logs/laravel.log` - Main application logs
- Look for `AutoEmailFetchService:` prefixed messages

### Key Log Messages
- `AutoEmailFetchService: Starting automatic email fetch for manager: {name}`
- `AutoEmailFetchService: Successfully processed {X} emails, stored {Y} new emails, created {Z} notifications`
- `AutoEmailFetchService: Created notification for email: {subject}`

### Database Tables
- `emails` - Stored email records
- `designers_inbox_notifications` - Manager notifications
- `email_fetch_logs` - Fetch operation history

## Troubleshooting

### Common Issues

1. **No emails being fetched**
   - Check IMAP credentials in `.env`
   - Verify IMAP server connectivity
   - Check logs for connection errors

2. **Notifications not showing**
   - Ensure user has manager role
   - Check if notifications are being created in database
   - Verify JavaScript console for errors

3. **Duplicate emails being stored**
   - Check message_id uniqueness
   - Review duplicate prevention logic in logs
   - Verify email parsing accuracy

4. **Scheduled command not running**
   - Check Laravel scheduler is running: `php artisan schedule:work`
   - Verify cron job setup
   - Check command lock mechanism

### Debug Commands
```bash
# Test IMAP connection
php artisan tinker
>>> app(\App\Services\DesignersInboxEmailService::class)->getEmailStats()

# Check notification count
php artisan tinker
>>> app(\App\Services\AutoEmailFetchService::class)->getUnreadNotificationsCount()

# Manual fetch test
php artisan emails:auto-fetch --max-results=5
```

## Production Deployment

### 1. Environment Variables
Ensure these are set in production:
```env
IMAP_PASSWORD=your_imap_password
MAIL_MAILER=smtp
MAIL_HOST=mail.orion-contracting.com
MAIL_PORT=993
MAIL_USERNAME=designers@orion-contracting.com
```

### 2. Cron Job
Add to crontab:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Queue Workers (if using queues)
```bash
php artisan queue:work --daemon
```

### 4. Log Monitoring
Monitor logs for errors:
```bash
tail -f storage/logs/laravel.log | grep "AutoEmailFetchService"
```

## Performance Considerations

- **Memory Usage**: Each fetch processes max 50 emails
- **Database Load**: Duplicate checks may impact performance with large email volumes
- **IMAP Load**: 3-minute intervals balance responsiveness with server load
- **Lock Mechanism**: Prevents concurrent operations, 10-minute timeout

## Security Notes

- **Manager Access**: Only managers can access designers inbox
- **CSRF Protection**: All API endpoints protected
- **Input Validation**: All inputs validated and sanitized
- **Error Handling**: Sensitive information not exposed in error messages

## Future Enhancements

- **Email Filtering**: Add rules for automatic email categorization
- **Priority Notifications**: Urgent email detection and special handling
- **Email Templates**: Custom notification templates
- **Bulk Actions**: Mark multiple emails as read/archived
- **Email Search**: Advanced search within designers inbox
- **Mobile Notifications**: Push notifications for mobile devices
