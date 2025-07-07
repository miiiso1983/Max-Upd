<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantMiddleware
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

        // If user is super_admin, deny access to tenant routes
        if ($user->hasRole('super_admin')) {
            abort(403, 'ليس لديك صلاحية للوصول إلى هذه الصفحة. يرجى استخدام لوحة تحكم Master Admin.');
        }

        // Check if user belongs to a tenant
        if (!$user->tenant_id) {
            return redirect()->route('login')
                           ->with('error', 'حسابك غير مرتبط بأي مستأجر. يرجى التواصل مع الإدارة.');
        }

        // Check if tenant is active
        if (!$user->tenant || !$user->tenant->is_active) {
            Auth::logout();
            return redirect()->route('login')
                           ->with('error', 'حساب المستأجر غير نشط. يرجى التواصل مع الإدارة.');
        }

        // Check if tenant license is valid
        if (!$user->tenant->hasValidLicense()) {
            Auth::logout();
            return redirect()->route('login')
                           ->with('error', 'انتهت صلاحية ترخيص المستأجر. يرجى التواصل مع الإدارة لتجديد الترخيص.');
        }

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                           ->with('error', 'حسابك غير نشط. يرجى التواصل مع إدارة المستأجر.');
        }

        return $next($request);
    }
}
