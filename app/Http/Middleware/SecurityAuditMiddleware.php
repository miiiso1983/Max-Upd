<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SecurityAuditService;

class SecurityAuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if IP is blocked
        if (SecurityAuditService::isIpBlocked($request->ip())) {
            return response()->json([
                'message' => 'Access denied. Your IP has been temporarily blocked due to suspicious activity.'
            ], 403);
        }

        // Check for SQL injection in request data
        $this->checkForSqlInjection($request);

        // Check for XSS in request data
        $this->checkForXss($request);

        // Log API requests for audit
        if ($request->is('api/*')) {
            SecurityAuditService::logSecurityEvent('api_request', [
                'method' => $request->method(),
                'url' => $request->url(),
                'route' => $request->route()?->getName(),
            ], $request->user(), $request);
        }

        // Log admin access
        if ($request->is('admin/*') || $request->is('super-admin/*')) {
            SecurityAuditService::logSecurityEvent('admin_access', [
                'url' => $request->url(),
                'route' => $request->route()?->getName(),
            ], $request->user(), $request);
        }

        $response = $next($request);

        // Log failed responses
        if ($response->getStatusCode() >= 400) {
            SecurityAuditService::logSecurityEvent('failed_request', [
                'status_code' => $response->getStatusCode(),
                'url' => $request->url(),
                'method' => $request->method(),
            ], $request->user(), $request);
        }

        return $response;
    }

    /**
     * Check for SQL injection patterns in request
     */
    private function checkForSqlInjection(Request $request): void
    {
        $inputs = $request->all();

        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                if (SecurityAuditService::checkSqlInjection($value, $request->user())) {
                    // Log additional context
                    SecurityAuditService::logSecurityEvent('sql_injection_detected', [
                        'field' => $key,
                        'url' => $request->url(),
                        'method' => $request->method(),
                    ], $request->user(), $request);
                }
            }
        }
    }

    /**
     * Check for XSS patterns in request
     */
    private function checkForXss(Request $request): void
    {
        $inputs = $request->all();

        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                if (SecurityAuditService::checkXss($value, $request->user())) {
                    // Log additional context
                    SecurityAuditService::logSecurityEvent('xss_detected', [
                        'field' => $key,
                        'url' => $request->url(),
                        'method' => $request->method(),
                    ], $request->user(), $request);
                }
            }
        }
    }
}
