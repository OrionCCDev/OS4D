<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Models\TaskEmailPreparation;

class QueueMonitorController extends Controller
{
    /**
     * Display queue status dashboard
     */
    public function index()
    {
        // Only managers can access queue monitor
        if (!auth()->user()->isManager()) {
            abort(403, 'Access denied. Only managers can access queue monitoring.');
        }

        // Get pending jobs
        $pendingJobs = DB::table('jobs')
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($job) {
                $payload = json_decode($job->payload, true);
                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'name' => $payload['displayName'] ?? 'Unknown',
                    'attempts' => $job->attempts,
                    'created_at' => date('Y-m-d H:i:s', $job->created_at),
                    'waiting_minutes' => round((time() - $job->created_at) / 60, 1),
                ];
            });

        // Get failed jobs
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($job) {
                $payload = json_decode($job->payload, true);
                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'connection' => $job->connection,
                    'queue' => $job->queue,
                    'name' => $payload['displayName'] ?? 'Unknown',
                    'exception' => substr($job->exception, 0, 500),
                    'failed_at' => $job->failed_at,
                ];
            });

        // Get email preparation stats
        $emailStats = DB::table('task_email_preparations')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Get stuck emails (processing for more than 10 minutes)
        $stuckEmails = TaskEmailPreparation::where('status', 'processing')
            ->where('created_at', '<', now()->subMinutes(10))
            ->with(['task', 'preparer'])
            ->get();

        // Get recent failed emails
        $failedEmails = TaskEmailPreparation::where('status', 'failed')
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->with(['task', 'preparer'])
            ->get();

        // Check if queue worker is running
        $queueWorkerRunning = $this->checkQueueWorkerStatus();

        return view('admin.queue-monitor', compact(
            'pendingJobs',
            'failedJobs',
            'emailStats',
            'stuckEmails',
            'failedEmails',
            'queueWorkerRunning'
        ));
    }

    /**
     * Retry a failed job
     */
    public function retryJob(Request $request, $jobId)
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Access denied.');
        }

        try {
            Artisan::call('queue:retry', ['id' => [$jobId]]);

            return redirect()->back()->with('success', 'Job has been queued for retry.');
        } catch (\Exception $e) {
            Log::error('Failed to retry job: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to retry job: ' . $e->getMessage());
        }
    }

    /**
     * Retry all failed jobs
     */
    public function retryAllJobs(Request $request)
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Access denied.');
        }

        try {
            Artisan::call('queue:retry', ['id' => ['all']]);

            return redirect()->back()->with('success', 'All failed jobs have been queued for retry.');
        } catch (\Exception $e) {
            Log::error('Failed to retry all jobs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to retry jobs: ' . $e->getMessage());
        }
    }

    /**
     * Delete a failed job
     */
    public function deleteJob(Request $request, $jobId)
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Access denied.');
        }

        try {
            Artisan::call('queue:forget', ['id' => $jobId]);

            return redirect()->back()->with('success', 'Failed job has been deleted.');
        } catch (\Exception $e) {
            Log::error('Failed to delete job: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete job: ' . $e->getMessage());
        }
    }

    /**
     * Flush all failed jobs
     */
    public function flushFailedJobs(Request $request)
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Access denied.');
        }

        try {
            Artisan::call('queue:flush');

            return redirect()->back()->with('success', 'All failed jobs have been deleted.');
        } catch (\Exception $e) {
            Log::error('Failed to flush failed jobs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to flush failed jobs: ' . $e->getMessage());
        }
    }

    /**
     * Reset stuck email preparations
     */
    public function resetStuckEmails(Request $request)
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Access denied.');
        }

        try {
            $updated = TaskEmailPreparation::where('status', 'processing')
                ->where('created_at', '<', now()->subMinutes(10))
                ->update([
                    'status' => 'failed',
                    'error_message' => 'Email sending timed out or stuck in processing. Reset by admin.'
                ]);

            return redirect()->back()->with('success', "Reset {$updated} stuck email(s).");
        } catch (\Exception $e) {
            Log::error('Failed to reset stuck emails: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reset stuck emails: ' . $e->getMessage());
        }
    }

    /**
     * Retry a failed email
     */
    public function retryEmail(Request $request, $emailId)
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Access denied.');
        }

        try {
            $emailPreparation = TaskEmailPreparation::findOrFail($emailId);

            // Reset status to draft so it can be sent again
            $emailPreparation->update([
                'status' => 'draft',
                'error_message' => null,
            ]);

            return redirect()->back()->with('success', 'Email has been reset to draft status. User can try sending again.');
        } catch (\Exception $e) {
            Log::error('Failed to retry email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to retry email: ' . $e->getMessage());
        }
    }

    /**
     * Check if queue worker is running
     */
    protected function checkQueueWorkerStatus()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $output = shell_exec('tasklist /FI "IMAGENAME eq php.exe" 2>NUL | findstr "queue:work"');
        } else {
            // Linux/Unix
            $output = shell_exec('ps aux | grep "queue:work" | grep -v grep');
        }

        return !empty($output);
    }

    /**
     * Get queue statistics as JSON
     */
    public function getStats()
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Access denied.');
        }

        $stats = [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'processing_emails' => TaskEmailPreparation::where('status', 'processing')->count(),
            'failed_emails' => TaskEmailPreparation::where('status', 'failed')->count(),
            'stuck_emails' => TaskEmailPreparation::where('status', 'processing')
                ->where('created_at', '<', now()->subMinutes(10))
                ->count(),
            'queue_worker_running' => $this->checkQueueWorkerStatus(),
        ];

        return response()->json($stats);
    }
}

