<?php

namespace App\Console\Commands;

use App\Services\EmailMonitoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorEmailReplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:monitor-replies {--provider= : Email service provider to setup monitoring for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor emails for replies and send notifications';

    protected $emailMonitoringService;

    /**
     * Create a new command instance.
     */
    public function __construct(EmailMonitoringService $emailMonitoringService)
    {
        parent::__construct();
        $this->emailMonitoringService = $emailMonitoringService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $provider = $this->option('provider');

        if ($provider) {
            $this->info("Setting up email monitoring for provider: {$provider}");
            $this->setupProviderMonitoring($provider);
            return 0;
        }

        $this->info('Starting comprehensive email reply monitoring...');

        try {
            $results = $this->emailMonitoringService->monitorForReplies();

            $this->info('Email monitoring completed:');
            $this->line("  - Webhook replies found: {$results['webhook_replies']}");
            $this->line("  - Scheduled replies found: {$results['scheduled_replies']}");
            
            if (!empty($results['errors'])) {
                $this->error('Errors encountered:');
                foreach ($results['errors'] as $error) {
                    $this->line("  - {$error}");
                }
            }

            // Display monitoring statistics
            $stats = $this->emailMonitoringService->getMonitoringStats();
            $this->info('Monitoring Statistics:');
            $this->line("  - Total sent emails: {$stats['total_sent_emails']}");
            $this->line("  - Total replies: {$stats['total_replies']}");
            $this->line("  - Reply rate: {$stats['reply_rate']}%");
            $this->line("  - Pending replies: {$stats['pending_replies']}");
            $this->line("  - Last check: {$stats['last_check']}");

            return 0;

        } catch (\Exception $e) {
            $this->error('Email monitoring failed: ' . $e->getMessage());
            Log::error('Email monitoring command failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Setup monitoring for a specific email provider
     */
    protected function setupProviderMonitoring(string $provider): void
    {
        $setupInstructions = $this->emailMonitoringService->setupEmailProviderMonitoring($provider);

        if (isset($setupInstructions['error'])) {
            $this->error($setupInstructions['error']);
            $this->line('Supported providers: ' . implode(', ', $setupInstructions['supported_providers']));
            return;
        }

        $this->info("Setup instructions for {$provider}:");
        $this->line("Webhook URL: {$setupInstructions['webhook_url']}");
        $this->line("Events to monitor: " . implode(', ', $setupInstructions['events']));
        
        $this->line('');
        $this->info('Configuration steps:');
        foreach ($setupInstructions['instructions'] as $step) {
            $this->line("  {$step}");
        }

        $this->line('');
        $this->info('After setup, test the webhook by sending a test email.');
        $this->line('You can also run: php artisan email:monitor-replies');
    }
}
