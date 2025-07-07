<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Skip if user is not authenticated
        if (!$user) {
            return $next($request);
        }

        // Skip if user doesn't have 2FA enabled
        if (!$user->hasTwoFactorAuthenticationEnabled()) {
            return $next($request);
        }

        // Skip if this is a 2FA related route
        if ($request->routeIs('two-factor.*')) {
            return $next($request);
        }

        // Skip if this is an API request with valid token
        if ($request->expectsJson() && $user->currentAccessToken()) {
            return $next($request);
        }

        // Check if user has completed 2FA challenge in this session
        if (!session('2fa_verified_' . $user->id)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Two factor authentication required',
                    'two_factor_required' => true
                ], 403);
            }

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
