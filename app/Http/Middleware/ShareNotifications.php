<?php

namespace App\Http\Middleware;

use App\Services\NotificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Share notifications data with all views
            View::share('unreadNotificationsCount', NotificationService::getUnreadCount($user->id));
            View::share('recentNotifications', NotificationService::getRecent($user->id, 5));

            // Share user preferences (simplified for now)
            View::share('userPreferences', []);
            View::share('themePreferences', ['theme' => 'default']);
        }

        return $next($request);
    }
}
