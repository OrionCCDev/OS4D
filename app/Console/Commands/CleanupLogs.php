<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanupLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up and rotate Laravel logs to prevent them from growing too large';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Starting log cleanup process...');

        $logPath = storage_path('logs/laravel.log');
        $maxSize = 50 * 1024 * 1024; // 50MB

        // Check if main log file exists and is too large
        if (File::exists($logPath)) {
            $size = File::size($logPath);
            $sizeInMB = round($size / 1024 / 1024, 2);

            $this->info("📊 Current log size: {$sizeInMB}MB");

            if ($size > $maxSize) {
                $this->info('🔄 Log file is too large, rotating...');

                // Create backup with timestamp
                $backupName = 'laravel-' . date('Y-m-d-H-i-s') . '.log';
                $backupPath = storage_path('logs/' . $backupName);

                try {
                    // Move current log to backup
                    File::move($logPath, $backupPath);

                    // Create new empty log file
                    File::put($logPath, '');
                    File::chmod($logPath, 0644);

                    $this->info("✅ Log rotated successfully to: {$backupName}");

                    // Log this action to the new log file
                    Log::info("Log file rotated by cleanup command. Previous size: {$sizeInMB}MB");

                } catch (\Exception $e) {
                    $this->error("❌ Failed to rotate log: " . $e->getMessage());
                    return 1;
                }
            } else {
                $this->info('✅ Log file size is within limits, no rotation needed');
            }
        } else {
            $this->warn('⚠️  Main log file not found');
        }

        // Clean up old backup logs (older than 7 days)
        $this->info('🧽 Cleaning up old backup logs...');
        $oldLogs = File::glob(storage_path('logs/laravel-*.log'));
        $deletedCount = 0;

        foreach ($oldLogs as $log) {
            $lastModified = File::lastModified($log);
            $daysOld = (time() - $lastModified) / (24 * 60 * 60);

            if ($daysOld > 7) {
                try {
                    File::delete($log);
                    $this->info("🗑️  Deleted old log: " . basename($log) . " (age: " . round($daysOld, 1) . " days)");
                    $deletedCount++;
                } catch (\Exception $e) {
                    $this->warn("⚠️  Failed to delete " . basename($log) . ": " . $e->getMessage());
                }
            }
        }

        // Clean up any other large log files
        $this->info('🔍 Checking for other large log files...');
        $allLogs = File::glob(storage_path('logs/*.log'));
        $otherLargeFiles = 0;

        foreach ($allLogs as $log) {
            if (basename($log) !== 'laravel.log' && File::size($log) > $maxSize) {
                $sizeInMB = round(File::size($log) / 1024 / 1024, 2);
                $this->warn("⚠️  Large log file found: " . basename($log) . " ({$sizeInMB}MB)");
                $otherLargeFiles++;
            }
        }

        if ($otherLargeFiles === 0) {
            $this->info('✅ No other large log files found');
        }

        // Summary
        $this->info('');
        $this->info('📋 Cleanup Summary:');
        $this->info("   • Old logs deleted: {$deletedCount}");
        $this->info("   • Other large files found: {$otherLargeFiles}");
        $this->info('✅ Log cleanup completed successfully!');

        return 0;
    }
}
