<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use App\Mail\UserGeneralEmail;

class UserEmailService
{
    /**
     * Send email using user's own email credentials
     * SECURITY: Uses Gmail OAuth instead of SMTP to avoid credential exposure
     */
    public function sendEmailFromUser(User $user, string $subject, string $body, array $recipients)
    {
        // Check if user has Gmail connected (preferred method)
        if ($user->hasGmailConnected()) {
            return $this->sendEmailViaGmailOAuth($user, $subject, $body, $recipients);
        }

        // Fallback to SMTP only if Gmail OAuth is not available
        if (!$user->email_credentials_configured) {
            throw new \Exception('User email credentials not configured. Please connect Gmail OAuth or set up your email settings first.');
        }

        // Use secure SMTP configuration
        return $this->sendEmailViaSecureSMTP($user, $subject, $body, $recipients);
    }

    /**
     * Send email via Gmail OAuth (preferred secure method)
     */
    private function sendEmailViaGmailOAuth(User $user, string $subject, string $body, array $recipients)
    {
        $gmailOAuthService = app(\App\Services\GmailOAuthService::class);

        $emailData = [
            'from' => $user->email,
            'from_name' => $user->name,
            'to' => $recipients,
            'subject' => $subject,
            'body' => view('emails.user-general-email-gmail', [
                'bodyContent' => $body,
                'senderName' => $user->name,
                'senderEmail' => $user->email,
                'toRecipients' => $recipients,
                'subject' => $subject,
            ])->render(),
        ];

        $success = $gmailOAuthService->sendEmail($user, $emailData);

        if ($success) {
            // Send notification to engineering
            $this->sendEngineeringNotification($user, $subject, $body, $recipients);
        }

        return $success;
    }

    /**
     * Send email via secure SMTP (fallback method)
     */
    private function sendEmailViaSecureSMTP(User $user, string $subject, string $body, array $recipients)
    {
        // Create a custom mail instance to avoid config exposure
        $mail = new UserGeneralEmail($subject, $body, $user, $recipients);

        // Use a secure mailer instance
        $mailer = app('mail.manager')->mailer('smtp');

        // Configure the mailer securely
        $mailer->getSwiftMailer()->getTransport()->setUsername($user->email_smtp_username);
        $mailer->getSwiftMailer()->getTransport()->setPassword(Crypt::decryptString($user->email_smtp_password));
        $mailer->getSwiftMailer()->getTransport()->setHost($user->email_smtp_host);
        $mailer->getSwiftMailer()->getTransport()->setPort($user->email_smtp_port);
        $mailer->getSwiftMailer()->getTransport()->setEncryption($user->email_smtp_encryption);

        // Send the email
        $mailer->to($recipients)
               ->cc('engineering@orion-contracting.com')
               ->send($mail);

        return true;
    }

    /**
     * Send notification to engineering@orion-contracting.com
     */
    private function sendEngineeringNotification(User $user, string $subject, string $body, array $recipients)
    {
        try {
            $notificationEmail = new UserGeneralEmail(
                '[NOTIFICATION] ' . $subject,
                $body,
                $user,
                $recipients
            );

            Mail::to('engineering@orion-contracting.com')
                ->send($notificationEmail);
        } catch (\Exception $e) {
            // Log error but don't fail the main email
            \Log::error('Failed to send engineering notification: ' . $e->getMessage());
        }
    }

    /**
     * Set up Gmail credentials for a user
     */
    public function setupGmailCredentials(User $user, string $appPassword)
    {
        $user->update([
            'email_provider' => 'gmail',
            'email_smtp_host' => 'smtp.gmail.com',
            'email_smtp_port' => 587,
            'email_smtp_username' => $user->email,
            'email_smtp_password' => Crypt::encryptString($appPassword),
            'email_smtp_encryption' => 'tls',
            'email_credentials_configured' => true,
            'email_credentials_updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Set up Outlook credentials for a user
     */
    public function setupOutlookCredentials(User $user, string $password)
    {
        $user->update([
            'email_provider' => 'outlook',
            'email_smtp_host' => 'smtp-mail.outlook.com',
            'email_smtp_port' => 587,
            'email_smtp_username' => $user->email,
            'email_smtp_password' => Crypt::encryptString($password),
            'email_smtp_encryption' => 'tls',
            'email_credentials_configured' => true,
            'email_credentials_updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Set up custom SMTP credentials for a user
     */
    public function setupCustomCredentials(User $user, array $credentials)
    {
        $user->update([
            'email_provider' => 'custom',
            'email_smtp_host' => $credentials['host'],
            'email_smtp_port' => $credentials['port'],
            'email_smtp_username' => $credentials['username'],
            'email_smtp_password' => Crypt::encryptString($credentials['password']),
            'email_smtp_encryption' => $credentials['encryption'] ?? 'tls',
            'email_credentials_configured' => true,
            'email_credentials_updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Test user email credentials
     */
    public function testUserCredentials(User $user)
    {
        try {
            $this->configureUserMailSettings($user);

            Mail::raw('This is a test email to verify your email credentials are working correctly.', function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Email Credentials Test - ' . $user->name)
                        ->from($user->email, $user->name);
            });

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Email credentials test failed: ' . $e->getMessage());
        }
    }

    /**
     * Get email provider options
     */
    public function getEmailProviderOptions()
    {
        return [
            'gmail' => [
                'name' => 'Gmail',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'instructions' => 'You need to enable 2-Factor Authentication and generate an App Password'
            ],
            'outlook' => [
                'name' => 'Outlook/Hotmail',
                'host' => 'smtp-mail.outlook.com',
                'port' => 587,
                'encryption' => 'tls',
                'instructions' => 'Use your regular Outlook password'
            ],
            'yahoo' => [
                'name' => 'Yahoo Mail',
                'host' => 'smtp.mail.yahoo.com',
                'port' => 587,
                'encryption' => 'tls',
                'instructions' => 'You need to generate an App Password'
            ],
            'custom' => [
                'name' => 'Custom SMTP',
                'host' => '',
                'port' => 587,
                'encryption' => 'tls',
                'instructions' => 'Enter your custom SMTP settings'
            ]
        ];
    }
}
