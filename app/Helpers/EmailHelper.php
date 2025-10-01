<?php

if (!function_exists('parseEmailBody')) {
    /**
     * Parse email body to extract HTML content from multipart emails
     */
    function parseEmailBody($body)
    {
        if (empty($body)) {
            return '';
        }

        // If it's a multipart email, extract the HTML part
        if (strpos($body, 'Content-Type: text/html') !== false) {
            // Split by boundary
            $parts = preg_split('/--[a-zA-Z0-9]+/', $body);

            foreach ($parts as $part) {
                if (strpos($part, 'Content-Type: text/html') !== false) {
                    // Extract HTML content after the headers
                    $lines = explode("\n", $part);
                    $htmlStart = false;
                    $htmlContent = '';

                    foreach ($lines as $line) {
                        if ($htmlStart) {
                            $htmlContent .= $line . "\n";
                        } elseif (strpos($line, 'Content-Type: text/html') !== false) {
                            $htmlStart = true;
                        }
                    }

                    // Clean up the HTML content
                    $htmlContent = trim($htmlContent);
                    $htmlContent = preg_replace('/^Content-Transfer-Encoding:.*$/m', '', $htmlContent);
                    $htmlContent = preg_replace('/^Content-Type:.*$/m', '', $htmlContent);
                    $htmlContent = preg_replace('/^\s*$/m', '', $htmlContent);

                    return trim($htmlContent);
                }
            }
        }

        // If it's plain text, convert to HTML
        if (strpos($body, 'Content-Type: text/plain') !== false) {
            $lines = explode("\n", $body);
            $textStart = false;
            $textContent = '';

            foreach ($lines as $line) {
                if ($textStart) {
                    $textContent .= $line . "\n";
                } elseif (strpos($line, 'Content-Type: text/plain') !== false) {
                    $textStart = true;
                }
            }

            // Convert plain text to HTML
            $textContent = htmlspecialchars(trim($textContent));
            $textContent = nl2br($textContent);

            return $textContent;
        }

        // If it's already HTML or plain text, return as is
        return $body;
    }
}

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
