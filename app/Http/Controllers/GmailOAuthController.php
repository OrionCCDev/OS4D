<?php

namespace App\Http\Controllers;

use App\Services\GmailOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GmailOAuthController extends Controller
{
    protected $gmailOAuthService;

    public function __construct(GmailOAuthService $gmailOAuthService)
    {
        $this->gmailOAuthService = $gmailOAuthService;
    }

    /**
     * Redirect to Gmail OAuth authorization
     */
    public function redirect()
    {
        try {
            $user = Auth::user();
            Log::info('Gmail OAuth redirect called by user: ' . $user->id . ' with email: ' . $user->email);

            $authUrl = $this->gmailOAuthService->getAuthUrl();
            Log::info('Gmail OAuth URL generated: ' . $authUrl);

            return redirect($authUrl);
        } catch (\Exception $e) {
            Log::error('Gmail OAuth redirect error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to initiate Gmail connection. Please try again.');
        }
    }

    /**
     * Handle Gmail OAuth callback
     */
    public function callback(Request $request)
    {
        try {
            $user = Auth::user();
            Log::info('Gmail OAuth callback method called by user: ' . $user->id . ' with email: ' . $user->email);
            Log::info('Callback request data: ' . json_encode($request->all()));

            $code = $request->get('code');
            $error = $request->get('error');

            if ($error) {
                Log::error('Gmail OAuth error: ' . $error);
                return redirect()->route('profile.edit')->with('error', 'Gmail connection was denied or failed.');
            }

            if (!$code) {
                Log::error('No authorization code received from Gmail');
                return redirect()->route('profile.edit')->with('error', 'No authorization code received from Gmail.');
            }

            Log::info('Processing callback for user: ' . $user->id);
            $success = $this->gmailOAuthService->handleCallback($code, $user);

            if ($success) {
                Log::info('Gmail connection successful for user: ' . $user->id);
                return redirect()->route('profile.edit')->with('success', 'Gmail account connected successfully! You can now send emails from your Gmail account.');
            } else {
                Log::error('Gmail connection failed for user: ' . $user->id);
                return redirect()->route('profile.edit')->with('error', 'Failed to connect Gmail account. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Gmail OAuth callback error: ' . $e->getMessage());
            Log::error('Gmail OAuth callback stack trace: ' . $e->getTraceAsString());
            return redirect()->route('profile.edit')->with('error', 'An error occurred while connecting Gmail. Please try again.');
        }
    }

    /**
     * Disconnect Gmail account
     */
    public function disconnect()
    {
        try {
            $user = Auth::user();
            $success = $this->gmailOAuthService->disconnectGmail($user);

            if ($success) {
                return redirect()->route('profile.edit')->with('success', 'Gmail account disconnected successfully.');
            } else {
                return redirect()->route('profile.edit')->with('error', 'Failed to disconnect Gmail account. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Gmail disconnect error: ' . $e->getMessage());
            return redirect()->route('profile.edit')->with('error', 'An error occurred while disconnecting Gmail. Please try again.');
        }
    }

    /**
     * Check Gmail connection status
     */
    public function status()
    {
        $user = Auth::user();
        $isConnected = $this->gmailOAuthService->isConnected($user);

        return response()->json([
            'connected' => $isConnected,
            'connected_at' => $user->gmail_connected_at,
        ]);
    }
}
