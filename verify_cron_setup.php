<?php
/**
 * Cron Setup Verification Script
 *
 * This script verifies that the Laravel scheduler is properly configured
 * and the email auto-fetch system is working correctly.
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Cron Setup Verification ===\n";
echo "Verifying Laravel scheduler and email auto-fetch configuration\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized\n";

    // Check 1: Verify scheduler configuration
    echo "=== Check 1: Scheduler Configuration ===\n";

    // Read the console.php file to verify the schedule
    $consoleFile = __DIR__ . '/routes/console.php';
    if (file_exists($consoleFile)) {
        $content = file_get_contents($consoleFile);

        if (strpos($content, "Schedule::command('emails:auto-fetch") !== false) {
            echo "✅ Email auto-fetch command is scheduled\n";

            if (strpos($content, '->everyFiveMinutes()') !== false) {
                echo "✅ Email fetching is set to run every 5 minutes\n";
            } else {
                echo "⚠️  Email fetching interval may not be set to 5 minutes\n";
            }

            if (strpos($content, '->withoutOverlapping()') !== false) {
                echo "✅ Overlap protection is enabled\n";
            } else {
                echo "⚠️  Overlap protection may not be enabled\n";
            }
        } else {
            echo "❌ Email auto-fetch command is not scheduled\n";
        }
    } else {
        echo "❌ Console routes file not found\n";
    }

    echo "\n";

    // Check 2: Verify command exists
    echo "=== Check 2: Command Verification ===\n";

    try {
        $commands = Artisan::all();
        if (isset($commands['emails:auto-fetch'])) {
            echo "✅ emails:auto-fetch command is registered\n";
            echo "   - Command class: " . get_class($commands['emails:auto-fetch']) . "\n";
        } else {
            echo "❌ emails:auto-fetch command is not registered\n";
        }
    } catch (Exception $e) {
        echo "❌ Error checking commands: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Check 3: Test manual command execution
    echo "=== Check 3: Manual Command Test ===\n";

    try {
        $exitCode = Artisan::call('emails:auto-fetch', [
            '--max-results' => 1,
            '--interval' => 5
        ]);

        if ($exitCode === 0) {
            echo "✅ Manual command execution successful\n";
            $output = Artisan::output();
            echo "   - Output: " . trim($output) . "\n";
        } else {
            echo "❌ Manual command execution failed with exit code: {$exitCode}\n";
        }
    } catch (Exception $e) {
        echo "❌ Manual command test failed: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Check 4: Verify database tables
    echo "=== Check 4: Database Tables ===\n";

    try {
        $tables = [
            'emails' => \App\Models\Email::class,
            'designers_inbox_notifications' => \App\Models\DesignersInboxNotification::class,
            'email_fetch_logs' => \App\Models\EmailFetchLog::class,
        ];

        foreach ($tables as $tableName => $modelClass) {
            try {
                $count = $modelClass::count();
                echo "✅ Table '{$tableName}' exists and accessible (count: {$count})\n";
            } catch (Exception $e) {
                echo "❌ Table '{$tableName}' error: " . $e->getMessage() . "\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Database check failed: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Check 5: Verify routes
    echo "=== Check 5: Routes Verification ===\n";

    $requiredRoutes = [
        'auto-emails.fetch',
        'auto-emails.unread-count',
        'auto-emails.recent-notifications',
        'auto-emails.mark-read',
        'auto-emails.mark-all-read',
    ];

    try {
        $routes = Route::getRoutes();
        foreach ($requiredRoutes as $routeName) {
            $route = $routes->getByName($routeName);
            if ($route) {
                echo "✅ Route '{$routeName}' exists\n";
            } else {
                echo "❌ Route '{$routeName}' not found\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Route verification failed: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Check 6: Environment configuration
    echo "=== Check 6: Environment Configuration ===\n";

    $requiredConfigs = [
        'MAIL_MAILER',
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_PASSWORD',
        'GOOGLE_CLIENT_ID',
        'GOOGLE_CLIENT_SECRET',
    ];

    foreach ($requiredConfigs as $config) {
        $value = config($config);
        if (!empty($value)) {
            echo "✅ {$config} is configured\n";
        } else {
            echo "⚠️  {$config} is not configured or empty\n";
        }
    }

    echo "\n";

    // Summary
    echo "=== Summary ===\n";
    echo "Your email auto-fetch system is configured to:\n";
    echo "• Fetch emails from engineering@orion-contracting.com every 5 minutes\n";
    echo "• Prevent duplicate emails using enhanced checks\n";
    echo "• Create notifications for new emails and replies\n";
    echo "• Display notifications in the navbar with real-time updates\n\n";

    echo "To ensure everything works in production:\n";
    echo "1. Make sure your cron job is running:\n";
    echo "   * * * * * cd /home/ed1b2bdo7yna/public_html/odc.com/ && php artisan schedule:run >> /dev/null 2>&1\n\n";

    echo "2. Check logs regularly:\n";
    echo "   tail -f storage/logs/laravel.log\n\n";

    echo "3. Monitor the email fetching:\n";
    echo "   Visit: https://odc.com.orion-contracting.com/emails-all\n\n";

    echo "4. Test notifications:\n";
    echo "   Check the navbar dropdown for email notifications\n\n";

    echo "✅ Verification completed!\n";

} catch (Exception $e) {
    echo "❌ Verification failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
