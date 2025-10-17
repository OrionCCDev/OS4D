<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\EmailSignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $signatureService = app(EmailSignatureService::class);

        return view('profile.edit', [
            'user' => $request->user(),
            'signatureService' => $signatureService,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's notification preferences.
     */
    public function updateNotificationPreferences(Request $request): RedirectResponse
    {
        $request->validate([
            'notification_sound_enabled' => 'boolean',
        ]);

        $user = $request->user();
        $user->notification_sound_enabled = $request->boolean('notification_sound_enabled');
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'notification-preferences-updated');
    }

    /**
     * Get signature preview data for AJAX requests
     */
    public function getSignaturePreview(Request $request)
    {
        $user = $request->user();
        $signatureService = app(EmailSignatureService::class);

        return response()->json([
            'html_signature' => $signatureService->getSignatureForEmail($user, 'html'),
            'plain_text_signature' => $signatureService->getSignatureForEmail($user, 'plain'),
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Prevent users from deleting their own accounts
        abort(403, 'You cannot delete your own account. Please contact an administrator.');
    }
}
