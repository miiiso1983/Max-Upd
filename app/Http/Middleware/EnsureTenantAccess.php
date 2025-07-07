<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow super admins to access everything
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if we have a current tenant
        $currentTenant = Tenant::current();

        if (!$currentTenant) {
            return response()->json([
                'message' => 'No tenant context found. Please access through a valid tenant domain.'
            ], 403);
        }

        // Check if user belongs to this tenant
        if ($user && $user->tenant_id !== $currentTenant->id) {
            return response()->json([
                'message' => 'You do not have access to this tenant.'
            ], 403);
        }

        return $next($request);
    }
}
