<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip license check for super admins
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Skip if license checking is disabled
        if (!config('app.license_check_enabled', true)) {
            return $next($request);
        }

        $currentTenant = Tenant::current();

        if (!$currentTenant) {
            return $next($request); // No tenant context, let other middleware handle
        }

        if (!$currentTenant->hasValidLicense()) {
            $gracePeriod = config('app.license_grace_period_days', 7);
            $daysExpired = $currentTenant->license_expires_at ?
                now()->diffInDays($currentTenant->license_expires_at, false) : 0;

            if ($daysExpired > $gracePeriod) {
                return response()->json([
                    'message' => 'License has expired. Please contact your administrator.',
                    'license_expired' => true,
                    'days_expired' => abs($daysExpired)
                ], 402); // Payment Required
            }

            // Within grace period, add warning header
            $response = $next($request);
            $response->headers->set('X-License-Warning', 'License expired but within grace period');
            $response->headers->set('X-Days-Until-Suspension', $gracePeriod - abs($daysExpired));

            return $response;
        }

        return $next($request);
    }
}
