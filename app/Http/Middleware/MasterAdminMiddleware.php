<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if user has super_admin role (Master Admin)
        if (!($user->hasRole('super-admin') || $user->hasRole('super_admin') || $user->is_super_admin)) {
            abort(403, 'ليس لديك صلاحية للوصول إلى لوحة تحكم Master Admin');
        }

        // Check if super admin is trying to access tenant-specific routes
        if (($user->hasRole('super-admin') || $user->hasRole('super_admin') || $user->is_super_admin) && $user->tenant_id) {
            // Super admin should not have tenant_id
            // This is a data integrity issue that should be fixed
            \Log::warning('Super Admin user has tenant_id', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id
            ]);
        }

        return $next($request);
    }
}
