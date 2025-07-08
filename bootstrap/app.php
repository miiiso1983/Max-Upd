<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/regulatory-affairs.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global middleware - temporarily disabled for debugging
        // $middleware->append([
        //     \App\Http\Middleware\SecurityHeadersMiddleware::class,
        //     \App\Http\Middleware\SecurityAuditMiddleware::class,
        // ]);

        // Middleware aliases
        $middleware->alias([
            'ensure-super-admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'ensure-tenant-access' => \App\Http\Middleware\EnsureTenantAccess::class,
            'check-license' => \App\Http\Middleware\CheckLicense::class,
            'two-factor' => \App\Http\Middleware\TwoFactorMiddleware::class,
            'rate-limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'sanitize-input' => \App\Http\Middleware\InputSanitizationMiddleware::class,
            'master-admin' => \App\Http\Middleware\MasterAdminMiddleware::class,
            'tenant' => \App\Http\Middleware\TenantMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // API middleware
        $middleware->group('api', [
            'rate-limit:120,1', // 120 requests per minute
            'sanitize-input',
        ]);

        // Web middleware
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocale::class,
            'sanitize-input',
            \App\Http\Middleware\ShareNotifications::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
