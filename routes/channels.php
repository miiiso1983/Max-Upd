<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Tenant-specific channels
Broadcast::channel('tenant.{tenantId}', function ($user, $tenantId) {
    return $user->tenant_id === (int) $tenantId;
});

// Notification channels
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Admin channels
Broadcast::channel('admin', function ($user) {
    return $user->hasRole(['admin', 'super_admin']);
});

// Super admin channels
Broadcast::channel('super-admin', function ($user) {
    return $user->hasRole('super_admin');
});
