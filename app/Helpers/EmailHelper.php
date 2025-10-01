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

        // Handle quoted-printable encoding first
        if (strpos($body, 'Content-Transfer-Encoding: quoted-printable') !== false) {
            $body = quoted_printable_decode($body);
        }

        // Ensure proper UTF-8 encoding
        $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');

        // Handle multipart emails with any boundary
        if (preg_match('/--([a-f0-9]+)/', $body, $matches)) {
            $boundary = '--' . $matches[1];
            // Split by the detected boundary
            $parts = explode($boundary, $body);

            foreach ($parts as $part) {
                // Skip empty parts
                if (trim($part) === '' || trim($part) === '--') {
                    continue;
                }

                // Look for HTML part
                if (strpos($part, 'Content-Type: text/html') !== false) {
                    // Extract HTML content after headers
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
                    $htmlContent = preg_replace('/^charset=.*$/m', '', $htmlContent);
                    $htmlContent = preg_replace('/^\s*$/m', '', $htmlContent);

                    $htmlContent = trim($htmlContent);

                    if (!empty($htmlContent) && strlen($htmlContent) > 50) {
                        return $htmlContent;
                    }
                }

                // Look for plain text part if no HTML found
                if (strpos($part, 'Content-Type: text/plain') !== false) {
                    $lines = explode("\n", $part);
                    $textStart = false;
                    $textContent = '';

                    foreach ($lines as $line) {
                        if ($textStart) {
                            $textContent .= $line . "\n";
                        } elseif (strpos($line, 'Content-Type: text/plain') !== false) {
                            $textStart = true;
                        }
                    }

                    // Clean up the text content
                    $textContent = trim($textContent);
                    $textContent = preg_replace('/^Content-Transfer-Encoding:.*$/m', '', $textContent);
                    $textContent = preg_replace('/^Content-Type:.*$/m', '', $textContent);
                    $textContent = preg_replace('/^charset=.*$/m', '', $textContent);

                    // Convert plain text to HTML
                    $textContent = htmlspecialchars($textContent);
                    $textContent = nl2br($textContent);

                    if (!empty($textContent) && strlen($textContent) > 20) {
                        return $textContent;
                    }
                }
            }
        }

        // If it's already HTML, return as is
        if (strpos($body, '<html') !== false || strpos($body, '<div') !== false || strpos($body, '<p') !== false) {
            return $body;
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

        // If it looks like plain text, convert it
        if (strlen($body) > 20 && !preg_match('/^--[a-f0-9]+/', $body)) {
            $textContent = htmlspecialchars($body);
            $textContent = nl2br($textContent);
            return $textContent;
        }

        // Fallback: return the body as is
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
