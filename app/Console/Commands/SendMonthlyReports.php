<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonthlyReportEmailService;
use Illuminate\Support\Facades\Log;

class SendMonthlyReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:send-monthly {--test : Send test email to specific user} {--email= : Email address for test mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send monthly performance reports to all users';

    protected $monthlyReportService;

    public function __construct(MonthlyReportEmailService $monthlyReportService)
    {
        parent::__construct();
        $this->monthlyReportService = $monthlyReportService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('test')) {
            $email = $this->option('email');

            if (!$email) {
                $this->error('Please provide an email address for test mode using --email=user@example.com');
                return 1;
            }

            $this->info("Sending test monthly report to: {$email}");

            try {
                $result = $this->monthlyReportService->sendTestMonthlyReport($email);
                $this->info("✅ {$result}");
                return 0;
            } catch (\Exception $e) {
                $this->error("❌ Error: " . $e->getMessage());
                return 1;
            }
        }

        $this->info('Starting monthly report generation and sending...');

        try {
            $result = $this->monthlyReportService->sendMonthlyReportsToAllUsers();

            $this->info("✅ Monthly reports sent successfully!");
            $this->info("📊 Summary:");
            $this->info("   - Total users: {$result['total_users']}");
            $this->info("   - Successfully sent: {$result['success_count']}");
            $this->info("   - Failed: {$result['error_count']}");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error sending monthly reports: " . $e->getMessage());
            Log::error('Monthly report sending failed: ' . $e->getMessage());
            return 1;
        }
    }
}
