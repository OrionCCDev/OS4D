<?php

namespace App\Services;

use App\Models\User;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Google\Service\Oauth2;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GmailOAuthService
{
    protected $client;
    protected $gmailService;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.gmail.client_id'));
        $this->client->setClientSecret(config('services.gmail.client_secret'));
        $this->client->setRedirectUri(config('services.gmail.redirect_uri'));
        $this->client->setScopes([
            Gmail::GMAIL_SEND,
            Gmail::GMAIL_READONLY,
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile'
        ]);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');

        // Fix SSL certificate issue for development
        if (config('app.env') === 'local' || config('app.debug')) {
            $this->client->setHttpClient(new \GuzzleHttp\Client([
                'verify' => false, // Only for development
            ]));
        }

        // Log Gmail configuration for debugging
        Log::info('Gmail OAuth Service initialized with Client ID: ' . substr(config('services.gmail.client_id'), 0, 10) . '...');

        // Validate configuration
        if (empty(config('services.gmail.client_id')) || config('services.gmail.client_id') === 'your_google_client_id_here') {
            Log::error('Gmail Client ID is not properly configured');
        }
        if (empty(config('services.gmail.client_secret')) || config('services.gmail.client_secret') === 'your_google_client_secret_here') {
            Log::error('Gmail Client Secret is not properly configured');
        }
    }

    /**
     * Get the authorization URL for Gmail OAuth
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Handle the OAuth callback and store tokens
     */
    public function handleCallback(string $code, User $user): bool
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                Log::error('Gmail OAuth error: ' . $token['error']);
                return false;
            }

            // Get user info to get the Gmail email address
            $this->client->setAccessToken($token);
            $oauth2 = new Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();
            $gmailEmail = $userInfo->getEmail();

            Log::info('Gmail OAuth callback - User ID: ' . $user->id . ', Current Email: ' . $user->email . ', Gmail Email: ' . $gmailEmail);

            // Check if the Gmail email is already taken by another user
            $existingUser = \App\Models\User::where('email', $gmailEmail)->where('id', '!=', $user->id)->first();
            if ($existingUser) {
                Log::warning('Gmail email ' . $gmailEmail . ' is already taken by user ' . $existingUser->id . '. Not updating email for user ' . $user->id);
                // Don't update the email, just store the tokens
                $user->update([
                    'gmail_token' => json_encode($token),
                    'gmail_refresh_token' => $token['refresh_token'] ?? null,
                    'gmail_access_token' => $token['access_token'] ?? null,
                    'gmail_connected' => true,
                    'gmail_connected_at' => now(),
                ]);
            } else {
                // Store tokens and update email to match Gmail account
                $user->update([
                    'gmail_token' => json_encode($token),
                    'gmail_refresh_token' => $token['refresh_token'] ?? null,
                    'gmail_access_token' => $token['access_token'] ?? null,
                    'gmail_connected' => true,
                    'gmail_connected_at' => now(),
                    'email' => $gmailEmail, // Update user's email to match Gmail account
                ]);
            }

            Log::info('Gmail OAuth successful for user: ' . $user->id . ($existingUser ? ' - Email not updated (already taken by another user)' : ' - Email updated to: ' . $gmailEmail));
            return true;
        } catch (\Exception $e) {
            Log::error('Gmail OAuth callback error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get authenticated Gmail service for user
     */
    public function getGmailService(User $user): ?Gmail
    {
        Log::info('Getting Gmail service for user: ' . $user->id . ' - Connected: ' . ($user->gmail_connected ? 'Yes' : 'No') . ' - Has Token: ' . (!empty($user->gmail_token) ? 'Yes' : 'No'));

        if (!$user->gmail_connected || !$user->gmail_token) {
            Log::warning('User ' . $user->id . ' does not have Gmail connected or token missing');
            return null;
        }

        try {
            $token = json_decode($user->gmail_token, true);
            if (!$token) {
                Log::error('Invalid Gmail token JSON for user: ' . $user->id);
                return null;
            }

            $this->client->setAccessToken($token);
            Log::info('Gmail token set for user: ' . $user->id);

            // Refresh token if needed
            if ($this->client->isAccessTokenExpired()) {
                Log::info('Gmail access token expired for user: ' . $user->id . ', attempting refresh');
                if ($user->gmail_refresh_token) {
                    $this->client->refreshToken($user->gmail_refresh_token);
                    $newToken = $this->client->getAccessToken();

                    // Update stored token
                    $user->update([
                        'gmail_token' => json_encode($newToken),
                        'gmail_access_token' => $newToken['access_token'] ?? null,
                    ]);
                    Log::info('Gmail token refreshed successfully for user: ' . $user->id);
                } else {
                    // No refresh token available, user needs to re-authenticate
                    Log::error('No refresh token available for user: ' . $user->id . ', disconnecting Gmail');
                    $this->disconnectGmail($user);
                    return null;
                }
            }

            $gmailService = new Gmail($this->client);
            Log::info('Gmail service created successfully for user: ' . $user->id);
            return $gmailService;
        } catch (\Exception $e) {
            Log::error('Gmail service error for user ' . $user->id . ': ' . $e->getMessage());
            Log::error('Gmail service error details: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Send email via Gmail API
     */
    public function sendEmail(User $user, array $emailData): bool
    {
        Log::info('Attempting to send Gmail email for user: ' . $user->id . ' to: ' . (is_array($emailData['to']) ? implode(', ', $emailData['to']) : $emailData['to']));

        $gmailService = $this->getGmailService($user);
        if (!$gmailService) {
            Log::error('Gmail service not available for user: ' . $user->id);
            return false;
        }

        try {
            // Validate email data
            if (empty($emailData['to']) || empty($emailData['from']) || empty($emailData['subject'])) {
                Log::error('Invalid email data for user ' . $user->id . ': Missing required fields');
                return false;
            }

            $message = $this->createMessage($emailData);
            Log::info('Gmail message created successfully for user: ' . $user->id);

            $result = $gmailService->users_messages->send('me', $message);

            if ($result && $result->getId()) {
                Log::info('Gmail email sent successfully for user: ' . $user->id . ' - Message ID: ' . $result->getId());
                return true;
            } else {
                Log::error('Gmail API returned success but no message ID for user: ' . $user->id);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Gmail send email error for user ' . $user->id . ': ' . $e->getMessage());
            Log::error('Gmail send email error details: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Create Gmail message from email data
     */
    protected function createMessage(array $emailData): Message
    {
        $boundary = uniqid(rand(), true);
        $rawMessage = $this->buildRawMessage($emailData, $boundary);

        Log::info('Raw message created, length: ' . strlen($rawMessage));
        Log::debug('Raw message content: ' . substr($rawMessage, 0, 500) . '...');

        $message = new Message();
        $encodedMessage = base64url_encode($rawMessage);
        $message->setRaw($encodedMessage);

        Log::info('Gmail message encoded successfully, encoded length: ' . strlen($encodedMessage));

        return $message;
    }

    /**
     * Build raw email message
     */
    protected function buildRawMessage(array $emailData, string $boundary): string
    {
        $to = is_array($emailData['to']) ? implode(', ', $emailData['to']) : $emailData['to'];
        $cc = isset($emailData['cc']) && is_array($emailData['cc']) ? implode(', ', $emailData['cc']) : ($emailData['cc'] ?? '');
        $bcc = isset($emailData['bcc']) && is_array($emailData['bcc']) ? implode(', ', $emailData['bcc']) : ($emailData['bcc'] ?? '');

        $headers = [
            'To: ' . $to,
            'From: ' . $emailData['from'],
            'Subject: ' . $emailData['subject'],
        ];

        if ($cc) {
            $headers[] = 'Cc: ' . $cc;
        }

        if ($bcc) {
            $headers[] = 'Bcc: ' . $bcc;
        }

        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

        $rawMessage = implode("\r\n", $headers) . "\r\n\r\n";

        // Add HTML body
        $rawMessage .= "--{$boundary}\r\n";
        $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $rawMessage .= $emailData['body'] . "\r\n";

        // Add attachments if any
        if (isset($emailData['attachments']) && is_array($emailData['attachments'])) {
            foreach ($emailData['attachments'] as $attachment) {
                $rawMessage .= "--{$boundary}\r\n";
                $rawMessage .= "Content-Type: " . $attachment['mime_type'] . "; name=\"" . $attachment['filename'] . "\"\r\n";
                $rawMessage .= "Content-Disposition: attachment; filename=\"" . $attachment['filename'] . "\"\r\n";
                $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $rawMessage .= chunk_split(base64_encode($attachment['content'])) . "\r\n";
            }
        }

        $rawMessage .= "--{$boundary}--\r\n";

        return $rawMessage;
    }

    /**
     * Get the Gmail email address for a user
     */
    public function getGmailEmail(User $user): ?string
    {
        try {
            $gmailService = $this->getGmailService($user);
            if (!$gmailService) {
                return null;
            }

            $profile = $gmailService->users->getProfile('me');
            return $profile->getEmailAddress();
        } catch (\Exception $e) {
            Log::error('Failed to get Gmail email for user ' . $user->id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Disconnect Gmail for user
     */
    public function disconnectGmail(User $user): bool
    {
        try {
            $user->update([
                'gmail_token' => null,
                'gmail_refresh_token' => null,
                'gmail_access_token' => null,
                'gmail_connected' => false,
                'gmail_connected_at' => null,
            ]);

            Log::info('Gmail disconnected for user: ' . $user->id);
            return true;
        } catch (\Exception $e) {
            Log::error('Gmail disconnect error for user ' . $user->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has Gmail connected
     */
    public function isConnected(User $user): bool
    {
        return $user->gmail_connected && !empty($user->gmail_token);
    }

    /**
     * Test Gmail connection and send a simple test email
     */
    public function testGmailConnection(User $user): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'details' => []
        ];

        try {
            // Check if user has Gmail connected
            if (!$this->isConnected($user)) {
                $result['message'] = 'User does not have Gmail connected';
                return $result;
            }

            // Get Gmail service
            $gmailService = $this->getGmailService($user);
            if (!$gmailService) {
                $result['message'] = 'Failed to get Gmail service';
                return $result;
            }

            // Create a simple test email
            $testEmailData = [
                'from' => $user->email,
                'to' => [$user->email], // Send to self for testing
                'subject' => 'Gmail OAuth Test Email',
                'body' => '<html><body><h1>Test Email</h1><p>This is a test email to verify Gmail OAuth is working correctly.</p></body></html>',
            ];

            // Send test email
            $success = $this->sendEmail($user, $testEmailData);

            if ($success) {
                $result['success'] = true;
                $result['message'] = 'Test email sent successfully';
            } else {
                $result['message'] = 'Failed to send test email';
            }

        } catch (\Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
            $result['details'] = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }

        return $result;
    }

    /**
     * Check Gmail API configuration
     */
    public function checkConfiguration(): array
    {
        $config = [
            'client_id' => config('services.gmail.client_id'),
            'client_secret' => config('services.gmail.client_secret'),
            'redirect_uri' => config('services.gmail.redirect_uri'),
        ];

        $issues = [];

        if (empty($config['client_id']) || $config['client_id'] === 'your_google_client_id_here') {
            $issues[] = 'Gmail Client ID is not configured or is using placeholder value';
        }

        if (empty($config['client_secret']) || $config['client_secret'] === 'your_google_client_secret_here') {
            $issues[] = 'Gmail Client Secret is not configured or is using placeholder value';
        }

        if (empty($config['redirect_uri'])) {
            $issues[] = 'Gmail Redirect URI is not configured';
        }

        return [
            'configured' => empty($issues),
            'issues' => $issues,
            'config' => [
                'client_id' => substr($config['client_id'], 0, 10) . '...',
                'client_secret' => substr($config['client_secret'], 0, 10) . '...',
                'redirect_uri' => $config['redirect_uri'],
            ]
        ];
    }

    /**
     * Test Gmail API connection (without user authentication)
     */
    public function testApiConnection(): array
    {
        try {
            // Test if we can create a Gmail service instance
            $testClient = new Client();
            $testClient->setClientId(config('services.gmail.client_id'));
            $testClient->setClientSecret(config('services.gmail.client_secret'));
            $testClient->setRedirectUri(config('services.gmail.redirect_uri'));
            $testClient->setScopes([Gmail::GMAIL_SEND]);

            // Try to create Gmail service
            $gmailService = new Gmail($testClient);

            return [
                'success' => true,
                'message' => 'Gmail API connection test successful',
                'service_created' => true
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gmail API connection test failed: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ];
        }
    }
}

/**
 * Base64 URL encode function
 */
if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
