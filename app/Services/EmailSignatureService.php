<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class EmailSignatureService
{
    /**
     * Generate HTML email signature for a user
     */
    public function generateSignature(User $user, $logoColor = 'blue')
    {
        $logoPath = $logoColor === 'white' ? 'DAssets/logo-white.webp' : 'DAssets/logo-blue.webp';
        $logoUrl = asset($logoPath);

        // Get user details
        $name = $user->name ?? 'User';
        $email = $user->email ?? '';
        $mobile = $user->mobile ?? $user->phone ?? '';
        $position = $this->getUserPosition($user);
        $department = $this->getUserDepartment($user);
        $userImage = $this->getUserImage($user);

        // Generate signature HTML
        $signature = $this->buildSignatureHTML($name, $email, $mobile, $position, $department, $logoUrl, $logoColor, $userImage);

        return $signature;
    }

    /**
     * Get user position from database or fallback to role-based position
     */
    protected function getUserPosition(User $user)
    {
        // Use actual position from database if available
        if (!empty($user->position)) {
            return $user->position;
        }

        // Fallback to role-based position
        $role = $user->role ?? 'user';
        $positions = [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'user' => 'Team Member',
            'designer' => 'Designer',
            'engineer' => 'Engineer',
            'contractor' => 'Contractor'
        ];

        return $positions[$role] ?? 'Team Member';
    }

    /**
     * Get user department
     */
    protected function getUserDepartment(User $user)
    {
        $role = $user->role ?? 'user';

        if (in_array($role, ['admin', 'manager'])) {
            return 'Engineering Department';
        }

        return 'Engineering Team';
    }

    /**
     * Get user image URL if available
     */
    protected function getUserImage(User $user)
    {
        // Check if user has a custom image (not default)
        if (!empty($user->img) && $user->img !== 'default.png' && $user->img !== 'default.jpg') {
            $imagePath = public_path('uploads/users/' . $user->img);
            if (file_exists($imagePath)) {
                return asset('uploads/users/' . $user->img);
            }
        }

        return null;
    }

    /**
     * Build the HTML signature
     */
    protected function buildSignatureHTML($name, $email, $mobile, $position, $department, $logoUrl, $logoColor, $userImage = null)
    {
        $textColor = $logoColor === 'white' ? '#ffffff' : '#333333';
        $accentColor = '#2563eb'; // Blue accent
        $lightGray = '#6b7280';

        return "
        <div style='font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: {$textColor}; max-width: 500px;'>
            <table cellpadding='0' cellspacing='0' border='0' style='border-collapse: collapse;'>
                <tr>
                    <td style='padding-right: 15px; vertical-align: top;'>
                        <img src='{$logoUrl}' alt='Orion Contracting' style='width: 80px; height: auto; max-height: 60px;' />
                    </td>
                    <td style='vertical-align: top;'>
                        <div style='margin-bottom: 8px;'>";

        // Add user image if available
        if ($userImage) {
            $signature .= "
                            <div style='display: flex; align-items: center; margin-bottom: 8px;'>
                                <img src='{$userImage}' alt='{$name}' style='width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; object-fit: cover;' />
                                <div>
                                    <strong style='color: {$accentColor}; font-size: 14px;'>{$name}</strong>
                                </div>
                            </div>";
        } else {
            $signature .= "
                            <strong style='color: {$accentColor}; font-size: 14px;'>{$name}</strong>";
        }

        $signature .= "
                        </div>
                        <div style='margin-bottom: 4px; color: {$lightGray};'>
                            <strong>{$position}</strong> | {$department}
                        </div>
                        <div style='margin-bottom: 2px;'>
                            📧 <a href='mailto:{$email}' style='color: {$accentColor}; text-decoration: none;'>{$email}</a>
                        </div>";

        if ($mobile) {
            $signature .= "
                        <div style='margin-bottom: 2px;'>
                            📱 <a href='tel:{$mobile}' style='color: {$accentColor}; text-decoration: none;'>{$mobile}</a>
                        </div>";
        }

        $signature .= "
                        <div style='margin-top: 8px; padding-top: 8px; border-top: 1px solid #e5e7eb; font-size: 11px; color: {$lightGray};'>
                            <strong>Orion Contracting Company</strong><br>
                            Engineering Department | Professional Services
                        </div>
                    </td>
                </tr>
            </table>
        </div>";

        return $signature;
    }

    /**
     * Generate plain text signature
     */
    public function generatePlainTextSignature(User $user)
    {
        $name = $user->name ?? 'User';
        $email = $user->email ?? '';
        $mobile = $user->mobile ?? $user->phone ?? '';
        $position = $this->getUserPosition($user);
        $department = $this->getUserDepartment($user);

        $signature = "\n\n--\n";
        $signature .= "{$name}\n";
        $signature .= "{$position} | {$department}\n";
        $signature .= "Email: {$email}\n";

        if ($mobile) {
            $signature .= "Mobile: {$mobile}\n";
        }

        $signature .= "\nOrion Contracting Company\n";
        $signature .= "Engineering Department | Professional Services\n";

        return $signature;
    }

    /**
     * Get signature for email sending
     */
    public function getSignatureForEmail(User $user, $format = 'html')
    {
        if ($format === 'html') {
            return $this->generateSignature($user, 'blue');
        }

        return $this->generatePlainTextSignature($user);
    }
}
