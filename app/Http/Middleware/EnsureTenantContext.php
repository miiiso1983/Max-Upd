<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is super admin, they can access without tenant context
        if ($user && ($user->hasRole('super_admin') || $user->is_super_admin)) {
            return $next($request);
        }

        // For regular users, ensure they have tenant context
        $tenantId = $user->tenant_id ?? session('tenant_id');

        if (!$tenantId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'يجب تحديد المستأجر أولاً'
                ], 400);
            }

            return redirect()->route('dashboard')->with('error', 'يجب تحديد المستأجر أولاً');
        }

        // Set tenant context in session if not already set
        if (!session('tenant_id')) {
            session(['tenant_id' => $tenantId]);
        }

        return $next($request);
    }
}
