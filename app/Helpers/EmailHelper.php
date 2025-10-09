<?php

namespace App\Helpers;

class EmailHelper
{
    /**
     * Decode email content from quoted-printable and other encodings
     *
     * @param string $content
     * @return string
     */
    public static function decodeEmailContent($content)
    {
        if (empty($content)) {
            return '';
        }

        // First, decode quoted-printable encoding
        $decoded = quoted_printable_decode($content);

        // If that didn't work or content is still encoded, try alternative methods
        if ($decoded === $content || self::containsQuotedPrintable($content)) {
            // Try manual decoding of common quoted-printable patterns
            $decoded = self::manualQuotedPrintableDecode($content);
        }

        // Clean up any remaining encoding artifacts
        $decoded = self::cleanEncodingArtifacts($decoded);

        return $decoded;
    }

    /**
     * Check if content contains quoted-printable encoding
     *
     * @param string $content
     * @return bool
     */
    private static function containsQuotedPrintable($content)
    {
        return strpos($content, '=3D') !== false ||
               strpos($content, '=20') !== false ||
               strpos($content, '=0A') !== false ||
               strpos($content, '=0D') !== false;
    }

    /**
     * Manual decoding of quoted-printable content
     *
     * @param string $content
     * @return string
     */
    private static function manualQuotedPrintableDecode($content)
    {
        // Replace common quoted-printable sequences
        $replacements = [
            '=3D' => '=',     // equals sign
            '=20' => ' ',     // space
            '=0A' => "\n",    // line feed
            '=0D' => "\r",    // carriage return
            '=3C' => '<',     // less than
            '=3E' => '>',     // greater than
            '=22' => '"',     // double quote
            '=27' => "'",     // single quote
            '=2C' => ',',     // comma
            '=2E' => '.',     // period
            '=2F' => '/',     // forward slash
            '=3A' => ':',     // colon
            '=3B' => ';',     // semicolon
            '=40' => '@',     // at symbol
            '=5B' => '[',     // left bracket
            '=5D' => ']',     // right bracket
            '=5F' => '_',     // underscore
            '=60' => '`',     // backtick
            '=7B' => '{',     // left brace
            '=7D' => '}',     // right brace
            '=7E' => '~',     // tilde
        ];

        // Apply replacements
        $decoded = str_replace(array_keys($replacements), array_values($replacements), $content);

        // Handle line breaks that might be encoded
        $decoded = preg_replace('/=\r?\n/', '', $decoded);

        return $decoded;
    }

    /**
     * Clean up encoding artifacts
     *
     * @param string $content
     * @return string
     */
    private static function cleanEncodingArtifacts($content)
    {
        // Remove any remaining = at end of lines (soft line breaks)
        $content = preg_replace('/=\s*\r?\n/', '', $content);

        // Clean up any remaining malformed = sequences
        $content = preg_replace('/=\s*$/', '', $content);

        // Fix any double spaces that might have been created
        $content = preg_replace('/\s{2,}/', ' ', $content);

        return trim($content);
    }

    /**
     * Extract plain text from HTML email content
     *
     * @param string $htmlContent
     * @return string
     */
    public static function extractPlainText($htmlContent)
    {
        // Decode first
        $decoded = self::decodeEmailContent($htmlContent);

        // Strip HTML tags
        $plainText = strip_tags($decoded);

        // Decode HTML entities
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');

        // Clean up whitespace
        $plainText = preg_replace('/\s+/', ' ', $plainText);

        return trim($plainText);
    }

    /**
     * Check if content is HTML
     *
     * @param string $content
     * @return bool
     */
    public static function isHtmlContent($content)
    {
        $decoded = self::decodeEmailContent($content);
        return preg_match('/<[^>]+>/', $decoded) === 1;
    }
}
