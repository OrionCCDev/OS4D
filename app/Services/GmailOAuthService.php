<?php

namespace App\Services;

use App\Models\User;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
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

            // Store tokens in user model
            $user->update([
                'gmail_token' => json_encode($token),
                'gmail_refresh_token' => $token['refresh_token'] ?? null,
                'gmail_connected' => true,
                'gmail_connected_at' => now(),
            ]);

            Log::info('Gmail OAuth successful for user: ' . $user->id);
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
            $message = $this->createMessage($emailData);
            Log::info('Gmail message created successfully for user: ' . $user->id);

            $result = $gmailService->users_messages->send('me', $message);
            Log::info('Gmail email sent successfully for user: ' . $user->id . ' - Message ID: ' . $result->getId());
            return true;
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
     * Disconnect Gmail for user
     */
    public function disconnectGmail(User $user): bool
    {
        try {
            $user->update([
                'gmail_token' => null,
                'gmail_refresh_token' => null,
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
}

/**
 * Base64 URL encode function
 */
if (!function_exists('base64url_encode')) {
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
