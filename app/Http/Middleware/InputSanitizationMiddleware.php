<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InputSanitizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize input data
        $this->sanitizeInput($request);

        return $next($request);
    }

    /**
     * Sanitize request input
     */
    protected function sanitizeInput(Request $request): void
    {
        $input = $request->all();
        $sanitized = $this->sanitizeArray($input);

        // Replace request input with sanitized data
        $request->replace($sanitized);
    }

    /**
     * Recursively sanitize array data
     */
    protected function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize string value
     */
    protected function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Trim whitespace
        $value = trim($value);

        // Remove potentially dangerous characters for SQL injection
        $value = str_replace(['<script', '</script>', 'javascript:', 'vbscript:', 'onload=', 'onerror='], '', $value);

        // Convert special characters to HTML entities for XSS protection
        // Only for non-HTML fields (you might want to be more selective)
        if (!$this->isHtmlField($value)) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }

    /**
     * Check if field should allow HTML content
     */
    protected function isHtmlField(string $fieldName): bool
    {
        // Define fields that should allow HTML content
        $htmlFields = ['description', 'content', 'notes', 'message'];

        // Check if field name is in the allowed HTML fields
        return in_array($fieldName, $htmlFields);
    }

    /**
     * Detect potential SQL injection patterns
     */
    protected function hasSqlInjectionPattern(string $value): bool
    {
        $patterns = [
            '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b|\bCREATE\b|\bALTER\b)/i',
            '/(\bOR\b|\bAND\b)\s+\d+\s*=\s*\d+/i',
            '/\'\s*(OR|AND)\s*\'/i',
            '/--\s*$/m',
            '/\/\*.*?\*\//s',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect potential XSS patterns
     */
    protected function hasXssPattern(string $value): bool
    {
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
            '/<iframe\b/i',
            '/<object\b/i',
            '/<embed\b/i',
            '/<form\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }
}
