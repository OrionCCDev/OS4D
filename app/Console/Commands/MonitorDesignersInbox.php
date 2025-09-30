<?php

namespace App\Console\Commands;

use App\Services\DesignersInboxMonitorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorDesignersInbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:monitor-designers-inbox {--test : Test IMAP connection only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor designers@orion-contracting.com inbox for new emails and replies';

    protected $monitorService;

    /**
     * Create a new command instance.
     */
    public function __construct(DesignersInboxMonitorService $monitorService)
    {
        parent::__construct();
        $this->monitorService = $monitorService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting designers inbox monitoring...');

        try {
            if ($this->option('test')) {
                // Test IMAP connection only
                $result = $this->monitorService->testConnection();

                if ($result['success']) {
                    $this->info('✅ IMAP Connection Test: ' . $result['message']);
                } else {
                    $this->error('❌ IMAP Connection Test Failed: ' . $result['message']);
                    return 1;
                }
            } else {
                // Monitor inbox
                $result = $this->monitorService->monitorInbox();

                if ($result['success']) {
                    $this->info('✅ Inbox monitoring completed successfully');
                    $this->info("📧 Emails processed: " . $result['emails_processed']);

                    if (!empty($result['emails'])) {
                        $this->info('📋 Processed emails:');
                        foreach ($result['emails'] as $email) {
                            $this->line("  - {$email['type']}: {$email['subject']} (from: {$email['from']})");
                        }
                    }
                } else {
                    $this->error('❌ Inbox monitoring failed: ' . $result['message']);
                    return 1;
                }
            }

        } catch (\Exception $e) {
            $this->error('Error during monitoring: ' . $e->getMessage());
            Log::error('Designers inbox monitoring error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
