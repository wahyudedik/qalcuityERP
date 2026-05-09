<?php

namespace App\Services;

/**
 * OutputEscaper - Helper service for safely displaying user-generated content.
 *
 * Provides methods to escape and sanitize output to prevent XSS attacks.
 */
class OutputEscaper
{
    /**
     * Escape HTML entities in a string.
     *
     * Use this for any user-generated content displayed in HTML context.
     */
    public static function html(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape for use in JavaScript strings.
     *
     * Use when inserting PHP variables into JavaScript.
     */
    public static function js(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Escape for use in HTML attributes.
     */
    public static function attr(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape for use in CSS context.
     */
    public static function css(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // Remove any CSS expressions and dangerous characters
        $value = preg_replace('/expression\s*\(/i', '', $value);
        $value = preg_replace('/javascript\s*:/i', '', $value);
        $value = preg_replace('/[<>"\'\\\\]/', '', $value);

        return $value;
    }

    /**
     * Escape for use in URL context.
     */
    public static function url(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // Filter out dangerous protocols
        $filtered = self::filterDangerousProtocols($value);

        return filter_var($filtered, FILTER_SANITIZE_URL) ?: '';
    }

    /**
     * Sanitize HTML allowing only safe tags.
     *
     * Use when you need to preserve some HTML formatting.
     */
    public static function sanitizeHtml(?string $value, array $allowedTags = []): string
    {
        if ($value === null) {
            return '';
        }

        // PHP 8.x: strip_tags harus menggunakan array, bukan string
        $defaultAllowed = ['p', 'br', 'strong', 'em', 'b', 'i', 'u', 'ul', 'ol', 'li', 'a', 'img'];
        $allowed = ! empty($allowedTags) ? $allowedTags : $defaultAllowed;

        return strip_tags($value, $allowed);
    }

    /**
     * Clean user-generated text content.
     *
     * Removes potentially dangerous content while preserving legitimate text.
     */
    public static function cleanText(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // Remove null bytes
        $value = str_replace(chr(0), '', $value);

        // Remove control characters except newlines and tabs
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        // Decode HTML entities first to catch encoded attacks
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Strip all HTML tags
        $value = strip_tags($value);

        // Escape the result
        return self::html($value);
    }

    /**
     * Filter out dangerous URL protocols.
     */
    protected static function filterDangerousProtocols(string $url): string
    {
        $dangerousProtocols = [
            'javascript:',
            'data:',
            'vbscript:',
            'file:',
        ];

        $lowerUrl = strtolower(trim($url));

        foreach ($dangerousProtocols as $protocol) {
            if (strpos($lowerUrl, $protocol) === 0) {
                return '#blocked';
            }
        }

        return $url;
    }

    /**
     * Display escaped value (convenience method for Blade).
     */
    public static function e(?string $value): string
    {
        return self::html($value);
    }

    /**
     * Recursively escape an array of values.
     */
    public static function array(array $data): array
    {
        $escaped = [];
        foreach ($data as $key => $value) {
            $escaped[$key] = is_array($value)
                ? self::array($value)
                : self::html((string) $value);
        }

        return $escaped;
    }
}
