<?php

namespace App\Mail;

use App\Mail\Transport\GmailTransport;
use App\Services\GmailOAuthService;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Auth;

class GmailMailManager extends MailManager
{
    /**
     * Create Gmail transport instance
     */
    protected function createGmailDriver(array $config)
    {
        $gmailOAuthService = app(GmailOAuthService::class);
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('No authenticated user for Gmail transport');
        }

        $transport = new GmailTransport($gmailOAuthService, $user);

        return new \Illuminate\Mail\Mailer(
            'gmail',
            $this->app['view'],
            $transport,
            $this->app['events']
        );
    }
}
