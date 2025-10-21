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
        if (!empty($user->img) && !in_array($user->img, ['default.png', 'default.jpg', '1.png'])) {
            $imagePath = public_path('uploads/users/' . $user->img);
            if (file_exists($imagePath)) {
                return asset('uploads/users/' . $user->img);
            }
        }

        // Return null to not show image in signature if using default
        // This keeps signatures clean for users without custom photos
        return null;
    }

    /**
     * Build the HTML signature
     */
    protected function buildSignatureHTML($name, $email, $mobile, $position, $department, $logoUrl, $logoColor, $userImage = null)
    {
        $textColor = $logoColor === 'white' ? '#ffffff' : '#333333';
        $primaryColor = '#1e40af'; // Deep blue
        $secondaryColor = '#3b82f6'; // Bright blue
        $accentOrange = '#f59e0b'; // Orange accent
        $lightBlue = '#dbeafe'; // Light blue background
        $lightGray = '#6b7280';

        $signature = '<div style="font-family: Arial, sans-serif; font-size: 13px; line-height: 1.5; color: ' . $textColor . '; max-width: 600px; margin: 20px 0;">';
        $signature .= '<table style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">';
        $signature .= '<tr>';
        $signature .= '<td style="vertical-align: top; padding: 0;">';
        $signature .= '<div style="display: flex; align-items: center; margin-bottom: 15px;">';
        $signature .= '<div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, ' . $primaryColor . ' 0%, ' . $secondaryColor . ' 100%); display: flex; align-items: center; justify-content: center; margin-right: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">';
        $signature .= '<span style="color: white; font-weight: bold; font-size: 18px;">' . strtoupper(substr($name, 0, 1)) . '</span>';
        $signature .= '</div>';
        $signature .= '<div>';
        $signature .= '<div style="font-size: 16px; font-weight: bold; color: ' . $primaryColor . '; margin-bottom: 2px;">' . $name . '</div>';
        $signature .= '<div style="font-size: 12px; color: ' . $accentOrange . '; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">' . $position . '</div>';
        $signature .= '</div>';
        $signature .= '</div>';

        $signature .= '<div style="background: white; border-radius: 8px; padding: 15px; margin-bottom: 15px; border-left: 4px solid ' . $accentOrange . ';">';
        $signature .= '<div style="margin-bottom: 8px;">';
        $signature .= '<span style="color: ' . $primaryColor . '; font-weight: bold;">📧</span>';
        $signature .= '<a href="mailto:' . $email . '" style="color: ' . $secondaryColor . '; text-decoration: none; margin-left: 8px; font-weight: 500;">' . $email . '</a>';
        $signature .= '</div>';

        if ($mobile) {
            $signature .= '<div style="margin-bottom: 8px;">';
            $signature .= '<span style="color: ' . $primaryColor . '; font-weight: bold;">📱</span>';
            $signature .= '<a href="tel:' . $mobile . '" style="color: ' . $secondaryColor . '; text-decoration: none; margin-left: 8px; font-weight: 500;">' . $mobile . '</a>';
            $signature .= '</div>';
        }

        $signature .= '</div>';

        $signature .= '<div style="background: linear-gradient(135deg, ' . $primaryColor . ' 0%, ' . $secondaryColor . ' 100%); color: white; padding: 12px 15px; border-radius: 8px; text-align: center;">';
        $signature .= '<div style="font-weight: bold; font-size: 14px; margin-bottom: 4px;">🏢 Orion Contracting Company</div>';
        $signature .= '<div style="font-size: 11px; opacity: 0.9;">Engineering Department | Professional Services</div>';
        $signature .= '</div>';
        $signature .= '</td>';
        $signature .= '</tr>';
        $signature .= '</table>';
        $signature .= '</div>';

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
