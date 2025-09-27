# Gmail OAuth Integration Documentation

## Overview
This document explains the Gmail OAuth integration implemented in the ODes task management application, allowing users to send emails from their own Gmail accounts when tasks are approved.

## Features Implemented

### 1. Gmail OAuth Service (`app/Services/GmailOAuthService.php`)
- Handles Gmail OAuth authentication flow
- Manages access and refresh tokens
- Provides methods for sending emails via Gmail API
- Handles token refresh automatically

### 2. User Model Updates (`app/Models/User.php`)
- Added Gmail OAuth fields: `gmail_token`, `gmail_refresh_token`, `gmail_connected`, `gmail_connected_at`
- Added helper methods: `hasGmailConnected()`, `getGmailService()`

### 3. Gmail OAuth Controller (`app/Http/Controllers/GmailOAuthController.php`)
- `redirect()` - Initiates Gmail OAuth flow
- `callback()` - Handles OAuth callback
- `disconnect()` - Disconnects Gmail account
- `status()` - Checks connection status

### 4. Gmail Transport (`app/Mail/Transport/GmailTransport.php`)
- Custom mail transport for Gmail API
- Converts Symfony Email to Gmail API format
- Handles attachments and multiple recipients

### 5. Task Controller Updates (`app/Http/Controllers/TaskController.php`)
- Updated `sendConfirmationEmail()` method to support Gmail OAuth
- Added `use_gmail` parameter to choose between SMTP and Gmail API

### 6. UI Components
- **Profile Page**: Gmail connection management (`resources/views/profile/partials/gmail-integration.blade.php`)
- **Email Preparation Form**: Gmail integration options (`resources/views/tasks/email-preparation.blade.php`)

### 7. Routes (`routes/web.php`)
- `GET /auth/gmail` - Start Gmail OAuth flow
- `GET /auth/gmail/callback` - OAuth callback
- `POST /auth/gmail/disconnect` - Disconnect Gmail
- `GET /auth/gmail/status` - Check connection status

## Configuration

### Environment Variables
Add these to your `.env` file:

```env
# Gmail OAuth Configuration
GMAIL_CLIENT_ID=your_google_client_id
GMAIL_CLIENT_SECRET=your_google_client_secret
GMAIL_REDIRECT_URI=https://yourdomain.com/auth/gmail/callback
```

### Google Cloud Console Setup
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Gmail API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URI: `https://yourdomain.com/auth/gmail/callback`
6. Download credentials JSON file

### Services Configuration (`config/services.php`)
```php
'gmail' => [
    'client_id' => env('GMAIL_CLIENT_ID'),
    'client_secret' => env('GMAIL_CLIENT_SECRET'),
    'redirect_uri' => env('GMAIL_REDIRECT_URI'),
],
```

## Installation Steps

### 1. Install Google API Client
```bash
composer require google/apiclient:^2.18.3
```

### 2. Run Migration
```bash
php artisan migrate
```

### 3. Update .env File
Add the Gmail OAuth credentials to your `.env` file.

### 4. Clear Configuration Cache
```bash
php artisan config:clear
php artisan cache:clear
```

## Usage

### For Users

#### Connecting Gmail Account
1. Go to Profile page (`/profile`)
2. Find "Gmail Integration" section
3. Click "Connect Gmail" button
4. Complete OAuth flow in popup window
5. Gmail account will be connected

#### Sending Emails via Gmail
1. When a task is approved and ready for email confirmation
2. Go to task details page
3. Click "Prepare Email" button
4. Fill in email details
5. Check "Send via Gmail" checkbox (if Gmail is connected)
6. Click "Send Email" button

### For Developers

#### Using Gmail Service
```php
use App\Services\GmailOAuthService;

$gmailService = app(GmailOAuthService::class);
$user = auth()->user();

// Check if user has Gmail connected
if ($gmailService->isConnected($user)) {
    // Send email via Gmail
    $emailData = [
        'from' => $user->email,
        'to' => ['recipient@example.com'],
        'subject' => 'Test Email',
        'body' => '<h1>Hello World</h1>',
    ];
    
    $success = $gmailService->sendEmail($user, $emailData);
}
```

#### Using in Controllers
```php
// In TaskController or any controller
$user = auth()->user();
$useGmail = $request->get('use_gmail', false) && $user->hasGmailConnected();

if ($useGmail) {
    // Use Gmail OAuth service
    $gmailService = app(GmailOAuthService::class);
    $success = $gmailService->sendEmail($user, $emailData);
} else {
    // Use regular Laravel Mail
    Mail::to($recipients)->send($mail);
}
```

## Security Considerations

### 1. Token Storage
- Access tokens are stored encrypted in the database
- Refresh tokens are stored separately for security
- Tokens are automatically refreshed when expired

### 2. OAuth Scopes
The integration requests minimal required scopes:
- `https://www.googleapis.com/auth/gmail.send` - Send emails
- `https://www.googleapis.com/auth/gmail.readonly` - Read email metadata
- `https://www.googleapis.com/auth/userinfo.email` - Get user email
- `https://www.googleapis.com/auth/userinfo.profile` - Get user profile

### 3. Data Privacy
- Only necessary email data is sent to Gmail API
- User tokens are not logged or exposed
- Gmail connection can be revoked at any time

## Troubleshooting

### Common Issues

#### 1. SSL Certificate Issues
If you encounter SSL certificate problems during Composer installation:
```bash
composer config --global disable-tls true
composer require google/apiclient:^2.18.3
composer config --global disable-tls false
```

#### 2. OAuth Redirect URI Mismatch
Ensure the redirect URI in Google Cloud Console exactly matches:
`https://yourdomain.com/auth/gmail/callback`

#### 3. Gmail API Not Enabled
Make sure Gmail API is enabled in Google Cloud Console:
1. Go to APIs & Services > Library
2. Search for "Gmail API"
3. Click "Enable"

#### 4. Token Refresh Issues
If tokens fail to refresh:
1. User needs to re-authenticate
2. Check if refresh token is valid
3. Verify client credentials

### Debug Mode
Enable debug logging in `.env`:
```env
LOG_LEVEL=debug
```

Check logs in `storage/logs/laravel.log` for Gmail OAuth related errors.

## API Endpoints

### Gmail OAuth Endpoints
- `GET /auth/gmail` - Start OAuth flow
- `GET /auth/gmail/callback` - OAuth callback
- `POST /auth/gmail/disconnect` - Disconnect Gmail
- `GET /auth/gmail/status` - Check connection status

### Task Email Endpoints
- `POST /tasks/{task}/send-confirmation-email` - Send confirmation email
  - Parameters: `use_gmail` (boolean) - Use Gmail API instead of SMTP

## File Structure

```
app/
├── Services/
│   └── GmailOAuthService.php
├── Http/Controllers/
│   └── GmailOAuthController.php
├── Mail/
│   └── Transport/
│       └── GmailTransport.php
└── Models/
    └── User.php (updated)

resources/views/
├── profile/partials/
│   └── gmail-integration.blade.php
└── tasks/
    └── email-preparation.blade.php (updated)

routes/
└── web.php (updated)

config/
└── services.php (updated)

database/migrations/
└── 2025_09_27_123821_add_gmail_oauth_fields_to_users_table.php
```

## Testing

### Manual Testing
1. Connect Gmail account via profile page
2. Create a task and assign it to a user
3. Approve the task
4. Prepare and send confirmation email with Gmail option enabled
5. Verify email is sent from user's Gmail account

### Unit Testing
```php
// Example test
public function test_gmail_oauth_connection()
{
    $user = User::factory()->create();
    $gmailService = app(GmailOAuthService::class);
    
    $this->assertFalse($gmailService->isConnected($user));
}
```

## Production Deployment

### 1. Update Environment Variables
Ensure all Gmail OAuth credentials are set in production `.env` file.

### 2. Update Google Cloud Console
- Add production domain to authorized origins
- Update redirect URI to production URL
- Verify OAuth consent screen is configured

### 3. Database Migration
Run migration on production:
```bash
php artisan migrate --force
```

### 4. Clear Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Support

For issues or questions regarding Gmail OAuth integration:
1. Check Laravel logs for error messages
2. Verify Google Cloud Console configuration
3. Ensure all environment variables are set correctly
4. Test OAuth flow in development environment first
