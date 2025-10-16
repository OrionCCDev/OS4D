<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class EmailWebhookController extends Controller
{
    /**
     * Trigger email fetch manually
     */
    public function triggerEmailFetch(Request $request)
    {
        try {
            // Verify the request (add your own security)
            $secret = $request->input('secret');
            $expectedSecret = env('EMAIL_WEBHOOK_SECRET', 'orion-email-2025');
            if ($secret !== $expectedSecret) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Run the email fetch command
            $exitCode = Artisan::call('emails:reliable-monitor', [
                '--max-results' => 50
            ]);

            $output = Artisan::output();

            Log::info('Email fetch triggered via webhook', [
                'exit_code' => $exitCode,
                'output' => $output
            ]);

            return response()->json([
                'success' => true,
                'exit_code' => $exitCode,
                'output' => $output
            ]);

        } catch (\Exception $e) {
            Log::error('Webhook email fetch failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check system status
     */
    public function status()
    {
        try {
            $recentEmails = \App\Models\Email::where('created_at', '>=', now()->subMinutes(10))->count();
            $recentNotifications = \App\Models\UnifiedNotification::where('created_at', '>=', now()->subMinutes(10))->count();

            return response()->json([
                'status' => 'ok',
                'recent_emails' => $recentEmails,
                'recent_notifications' => $recentNotifications,
                'last_check' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
