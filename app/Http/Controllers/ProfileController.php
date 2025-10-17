<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\EmailSignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
     * Update the user's profile image
     */
    public function updateImage(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user = $request->user();

        try {
            // Delete old image if it exists and is not default
            if ($user->img && !in_array($user->img, ['default.png', 'default.jpg', '1.png'])) {
                $oldImagePath = public_path('uploads/users/' . $user->img);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Handle file upload
            $file = $request->file('profile_image');
            $filename = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Ensure directory exists
            $uploadDir = public_path('uploads/users');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move uploaded file
            $file->move($uploadDir, $filename);

            // Update user record
            $user->img = $filename;
            $user->save();

            return Redirect::route('profile.edit')->with('status', 'profile-image-updated');

        } catch (\Exception $e) {
            return Redirect::route('profile.edit')
                ->withErrors(['profile_image' => 'Failed to upload image. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the user's profile image
     */
    public function removeImage(Request $request): RedirectResponse
    {
        $user = $request->user();

        try {
            // Delete current image if it exists and is not default
            if ($user->img && !in_array($user->img, ['default.png', 'default.jpg', '1.png'])) {
                $imagePath = public_path('uploads/users/' . $user->img);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Reset to default image
            $user->img = '1.png'; // Default avatar
            $user->save();

            return Redirect::route('profile.edit')->with('status', 'profile-image-removed');

        } catch (\Exception $e) {
            return Redirect::route('profile.edit')
                ->withErrors(['profile_image' => 'Failed to remove image. Please try again.']);
        }
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
