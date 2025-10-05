<?php

require_once 'vendor/autoload.php';

echo "=== Logo Upload Test ===\n\n";

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Check if logo exists locally
    $localLogoPath = 'public/uploads/logo-blue.webp';
    echo "1. Checking local logo file...\n";
    echo "   Local path: {$localLogoPath}\n";
    echo "   Exists: " . (file_exists($localLogoPath) ? 'Yes' : 'No') . "\n";

    if (file_exists($localLogoPath)) {
        echo "   Size: " . filesize($localLogoPath) . " bytes\n";
        echo "   ✅ Local logo file exists\n";
    } else {
        echo "   ❌ Local logo file not found\n";
    }
    echo "\n";

    // Check production path
    $productionLogoPath = '/home/edlb2bdo7yna/public_html/odc.com/public/uploads/logo-blue.webp';
    echo "2. Checking production logo path...\n";
    echo "   Production path: {$productionLogoPath}\n";
    echo "   Exists: " . (file_exists($productionLogoPath) ? 'Yes' : 'No') . "\n";

    if (file_exists($productionLogoPath)) {
        echo "   Size: " . filesize($productionLogoPath) . " bytes\n";
        echo "   ✅ Production logo file exists\n";
    } else {
        echo "   ❌ Production logo file not found\n";
        echo "   This is why email sending fails!\n";
    }
    echo "\n";

    // Check uploads directory
    $uploadsDir = public_path('uploads');
    echo "3. Checking uploads directory...\n";
    echo "   Directory: {$uploadsDir}\n";
    echo "   Exists: " . (is_dir($uploadsDir) ? 'Yes' : 'No') . "\n";

    if (is_dir($uploadsDir)) {
        $files = scandir($uploadsDir);
        $logoFiles = array_filter($files, function($f) {
            return strpos($f, 'logo') !== false;
        });
        echo "   Logo files found: " . implode(', ', $logoFiles) . "\n";
    } else {
        echo "   ❌ Uploads directory does not exist!\n";
    }
    echo "\n";

    // Check if we can create the directory and copy the file
    echo "4. Attempting to fix logo issue...\n";

    if (!is_dir($uploadsDir)) {
        echo "   Creating uploads directory...\n";
        if (mkdir($uploadsDir, 0755, true)) {
            echo "   ✅ Uploads directory created\n";
        } else {
            echo "   ❌ Failed to create uploads directory\n";
        }
    }

    if (file_exists($localLogoPath) && !file_exists($productionLogoPath)) {
        echo "   Copying logo file to production...\n";
        if (copy($localLogoPath, $productionLogoPath)) {
            echo "   ✅ Logo file copied to production\n";
        } else {
            echo "   ❌ Failed to copy logo file\n";
        }
    }

    // Final check
    echo "\n5. Final verification...\n";
    if (file_exists($productionLogoPath)) {
        echo "   ✅ Logo file is now available at: {$productionLogoPath}\n";
        echo "   ✅ Email sending should now work!\n";
    } else {
        echo "   ❌ Logo file still not found\n";
        echo "   ❌ Email sending will continue to fail\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
