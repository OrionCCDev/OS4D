<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserEmailService;

class UserEmailSettingsController extends Controller
{
    protected $userEmailService;

    public function __construct(UserEmailService $userEmailService)
    {
        $this->userEmailService = $userEmailService;
    }

    /**
     * Show email settings form
     */
    public function index()
    {
        $user = Auth::user();
        $providers = $this->userEmailService->getEmailProviderOptions();

        return view('emails.user-email-settings', compact('user', 'providers'));
    }

    /**
     * Save Gmail credentials
     */
    public function saveGmailCredentials(Request $request)
    {
        $request->validate([
            'app_password' => 'required|string|min:16'
        ]);

        try {
            $user = Auth::user();
            $this->userEmailService->setupGmailCredentials($user, $request->app_password);

            return redirect()->back()->with('success', 'Gmail credentials saved successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save Gmail credentials: ' . $e->getMessage());
        }
    }

    /**
     * Save Outlook credentials
     */
    public function saveOutlookCredentials(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $this->userEmailService->setupOutlookCredentials($user, $request->password);

            return redirect()->back()->with('success', 'Outlook credentials saved successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save Outlook credentials: ' . $e->getMessage());
        }
    }

    /**
     * Save custom SMTP credentials
     */
    public function saveCustomCredentials(Request $request)
    {
        $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'required|in:tls,ssl,none'
        ]);

        try {
            $user = Auth::user();
            $this->userEmailService->setupCustomCredentials($user, $request->all());

            return redirect()->back()->with('success', 'Custom SMTP credentials saved successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save custom credentials: ' . $e->getMessage());
        }
    }

    /**
     * Test email credentials
     */
    public function testCredentials()
    {
        try {
            $user = Auth::user();
            $this->userEmailService->testUserCredentials($user);

            return redirect()->back()->with('success', 'Email credentials test successful! Check your email for the test message.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Email credentials test failed: ' . $e->getMessage());
        }
    }

    /**
     * Send email from user's account
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'to_emails' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            $toEmails = array_filter(array_map('trim', explode(',', $request->to_emails)));

            $this->userEmailService->sendEmailFromUser(
                $user,
                $request->subject,
                $request->body,
                $toEmails
            );

            return redirect()->back()->with('success', 'Email sent successfully from your account!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}
