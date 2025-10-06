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
     */
    public function sendEmailFromUser(User $user, string $subject, string $body, array $recipients)
    {
        // Check if user has email credentials configured
        if (!$user->email_credentials_configured) {
            throw new \Exception('User email credentials not configured. Please set up your email settings first.');
        }

        // Configure mail settings for this user
        $this->configureUserMailSettings($user);

        // Create and send email
        $email = new UserGeneralEmail($subject, $body, $user, $recipients);

        Mail::to($recipients)
            ->cc('engineering@orion-contracting.com')
            ->send($email);

        return true;
    }

    /**
     * Configure mail settings for a specific user
     */
    private function configureUserMailSettings(User $user)
    {
        // Decrypt the password
        $password = $user->email_smtp_password ? Crypt::decryptString($user->email_smtp_password) : null;

        // Configure mail settings
        Config::set([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $user->email_smtp_host,
            'mail.mailers.smtp.port' => $user->email_smtp_port,
            'mail.mailers.smtp.username' => $user->email_smtp_username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.encryption' => $user->email_smtp_encryption,
            'mail.from.address' => $user->email,
            'mail.from.name' => $user->name,
        ]);
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
