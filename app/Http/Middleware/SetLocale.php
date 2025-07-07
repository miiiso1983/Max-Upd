<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Services\LocalizationService;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from various sources in order of priority
        $locale = $this->getLocale($request);
        
        // Validate locale
        if (!$this->isValidLocale($locale)) {
            $locale = config('app.locale', 'ar');
        }
        
        // Set application locale
        App::setLocale($locale);
        
        // Store in session for persistence
        Session::put('locale', $locale);
        
        // Set Carbon locale for date formatting
        \Carbon\Carbon::setLocale($locale);
        
        return $next($request);
    }

    /**
     * Get locale from request
     */
    private function getLocale(Request $request): string
    {
        // 1. Check URL parameter
        if ($request->has('lang')) {
            return $request->get('lang');
        }
        
        // 2. Check session
        if (Session::has('locale')) {
            return Session::get('locale');
        }
        
        // 3. Check user preference (if authenticated)
        if ($request->user() && isset($request->user()->settings['language'])) {
            return $request->user()->settings['language'];
        }
        
        // 4. Check Accept-Language header
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            $preferredLanguage = $this->parseAcceptLanguage($acceptLanguage);
            if ($preferredLanguage) {
                return $preferredLanguage;
            }
        }
        
        // 5. Default to Arabic
        return config('app.locale', 'ar');
    }

    /**
     * Check if locale is valid
     */
    private function isValidLocale(string $locale): bool
    {
        $availableLocales = array_keys(LocalizationService::getAvailableLanguages());
        return in_array($locale, $availableLocales);
    }

    /**
     * Parse Accept-Language header
     */
    private function parseAcceptLanguage(string $acceptLanguage): ?string
    {
        $availableLocales = array_keys(LocalizationService::getAvailableLanguages());
        
        // Parse the Accept-Language header
        $languages = [];
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, ';') !== false) {
                [$lang, $quality] = explode(';', $part, 2);
                $quality = (float) str_replace('q=', '', $quality);
            } else {
                $lang = $part;
                $quality = 1.0;
            }
            
            $lang = trim($lang);
            $languages[$lang] = $quality;
        }
        
        // Sort by quality
        arsort($languages);
        
        // Find the first available language
        foreach ($languages as $lang => $quality) {
            // Check exact match
            if (in_array($lang, $availableLocales)) {
                return $lang;
            }
            
            // Check language part only (e.g., 'en' from 'en-US')
            $langPart = explode('-', $lang)[0];
            if (in_array($langPart, $availableLocales)) {
                return $langPart;
            }
        }
        
        return null;
    }
}
