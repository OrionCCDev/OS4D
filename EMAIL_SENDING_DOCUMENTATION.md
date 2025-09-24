# Email Sending System Documentation

## Overview
This document explains how the email sending system works in the ODes task management application, including configuration, email preparation, and sending process.

## Email Configuration

### Current Configuration
The application uses Laravel's built-in mail system with the following configuration:

**File:** `config/mail.php`

```php
'default' => env('MAIL_MAILER', 'log'),
```

**Supported Mail Drivers:**
- `smtp` - SMTP server
- `sendmail` - Sendmail command
- `mailgun` - Mailgun service
- `ses` - Amazon SES
- `postmark` - Postmark service
- `resend` - Resend service
- `log` - Log to file (default)
- `array` - Array driver for testing

### Environment Variables Required

To configure email sending, you need to set these environment variables in your `.env` file:

```env
# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Your Company Name"
```

### SMTP Configuration Example
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail@gmail.com
MAIL_FROM_NAME="ODes Task Management"
```

### Gmail Setup (if using Gmail)
1. Enable 2-factor authentication on your Gmail account
2. Generate an App Password:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate a password for "Mail"
   - Use this password in `MAIL_PASSWORD`

## Email Sending Process

### 1. Email Preparation Flow

**Route:** `tasks/{task}/prepare-email`
**Controller:** `TaskController@showEmailPreparationForm`

**Prerequisites:**
- User must be assigned to the task (`$task->assigned_to === Auth::id()`)
- Task status must be `ready_for_email`

**Process:**
1. User accesses the email preparation form
2. User fills in email details (recipients, subject, body, attachments)
3. User can save as draft or send immediately
4. Email preparation is stored in `task_email_preparations` table

### 2. Email Sending Flow

**Route:** `tasks/{task}/send-confirmation-email`
**Controller:** `TaskController@sendConfirmationEmail`

**Process:**
1. Validation of user permissions and task status
2. Retrieval of email preparation data
3. Parsing of email addresses (TO, CC, BCC)
4. Creation of `TaskConfirmationMail` instance
5. Sending via Laravel Mail facade
6. Update of email preparation status to 'sent'
7. Update of task status to 'completed'

### 3. Email Template

**File:** `resources/views/emails/task-confirmation.blade.php`

The email template includes:
- Professional header with gradient background
- Task completion confirmation message
- Custom message from the user
- Task details table
- Completion information
- Professional footer

**Template Variables:**
- `$task` - Task model instance
- `$emailPreparation` - Email preparation data
- `$sender` - User who sent the email

## Database Schema

### Task Email Preparations Table
```sql
CREATE TABLE task_email_preparations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    task_id BIGINT NOT NULL,
    prepared_by BIGINT NOT NULL,
    to_emails TEXT NOT NULL,
    cc_emails TEXT NULL,
    bcc_emails TEXT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    attachments JSON NULL,
    status ENUM('draft', 'sent') DEFAULT 'draft',
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (prepared_by) REFERENCES users(id) ON DELETE CASCADE
);
```

## Email Classes

### TaskConfirmationMail
**File:** `app/Mail/TaskConfirmationMail.php`

**Features:**
- Extends Laravel's Mailable class
- Handles email envelope (subject, from address)
- Uses task-confirmation email template
- Supports file attachments
- Includes task and sender information

**Key Methods:**
- `envelope()` - Sets email subject and sender
- `content()` - Defines email template and data
- `attachments()` - Handles file attachments

## Security Features

### Access Control
- Only assigned users can prepare emails for their tasks
- Only tasks with `ready_for_email` status can have emails prepared
- CSRF protection on all forms
- File upload validation (10MB limit per file)

### Email Validation
- Email address format validation
- Required field validation
- File type restrictions
- Size limitations

## Troubleshooting

### Common Issues

1. **Emails not sending**
   - Check `MAIL_MAILER` setting
   - Verify SMTP credentials
   - Check Laravel logs (`storage/logs/laravel.log`)

2. **SMTP Authentication Failed**
   - Verify username/password
   - Check if 2FA is enabled (use app password)
   - Verify SMTP server settings

3. **Attachments not working**
   - Check file permissions in `storage/app/`
   - Verify file size limits
   - Check file type restrictions

### Debug Mode
Set `MAIL_MAILER=log` to log emails instead of sending them. Check `storage/logs/laravel.log` for email content.

### Testing Email Configuration
```bash
# Test email configuration
php artisan tinker
>>> Mail::raw('Test email', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

## Email Service Providers

### Recommended Providers

1. **Mailgun** (Recommended for production)
   - Reliable delivery
   - Good analytics
   - Easy setup
   - Free tier available

2. **Amazon SES**
   - Cost-effective
   - High deliverability
   - Scalable
   - Requires AWS account

3. **Postmark**
   - Developer-friendly
   - Good deliverability
   - Transactional emails focus

4. **Resend**
   - Modern API
   - Good developer experience
   - Competitive pricing

### Configuration Examples

**Mailgun:**
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=your-domain.mailgun.org
MAILGUN_SECRET=your-mailgun-secret
```

**Amazon SES:**
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
```

## Best Practices

### Email Content
- Use professional subject lines
- Keep messages concise and clear
- Include task details for context
- Use proper email signatures

### Security
- Validate all email addresses
- Sanitize user input
- Use CSRF protection
- Implement rate limiting

### Performance
- Use queue for bulk emails
- Optimize email templates
- Monitor delivery rates
- Handle failures gracefully

## Monitoring and Logging

### Email Logs
- Laravel logs email sending attempts
- Check `storage/logs/laravel.log` for errors
- Monitor delivery rates through email service provider

### Database Tracking
- Email preparation status tracked in database
- Sent timestamps recorded
- Attachment information stored

## Future Enhancements

### Planned Features
1. Email templates management
2. Bulk email sending
3. Email scheduling
4. Delivery tracking
5. Email analytics dashboard
6. Custom email signatures
7. Email reply handling

### Integration Possibilities
1. CRM integration
2. Calendar integration
3. Document management
4. Notification systems
5. API endpoints for external systems
