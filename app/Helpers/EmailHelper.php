<?php

if (!function_exists('cleanEmailBody')) {
    /**
     * Clean and format email body for display
     */
    function cleanEmailBody(string $body): string
    {
        // Decode quoted-printable if needed
        if (strpos($body, 'quoted-printable') !== false) {
            $body = quoted_printable_decode($body);
        }
        
        // Remove MIME boundaries and headers
        $body = preg_replace('/--[a-zA-Z0-9]+--/', '', $body);
        $body = preg_replace('/Content-Type:.*?\r?\n/', '', $body);
        $body = preg_replace('/Content-Transfer-Encoding:.*?\r?\n/', '', $body);
        $body = preg_replace('/Content-Disposition:.*?\r?\n/', '', $body);
        
        // Clean up excessive whitespace
        $body = preg_replace('/\s+/', ' ', $body);
        $body = preg_replace('/\r?\n\s*\r?\n/', "\n\n", $body);
        
        // If it's HTML, clean it up
        if (strpos($body, '<html') !== false || strpos($body, '<div') !== false) {
            // Remove script tags for security
            $body = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $body);
            
            // Clean up HTML
            $body = preg_replace('/\s+/', ' ', $body);
            $body = preg_replace('/>\s+</', '><', $body);
        }
        
        return trim($body);
    }
}
