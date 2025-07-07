<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login-simple');
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'tenant_domain' => 'nullable|string',
        ]);

        // Set tenant domain in request if provided
        if ($request->tenant_domain) {
            $request->headers->set('X-Tenant-Domain', $request->tenant_domain);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Check if user is super admin or belongs to current tenant
        if (!$user->isSuperAdmin()) {
            $currentTenant = Tenant::current();

            // If no tenant context, try to find user's tenant
            if (!$currentTenant && $user->tenant_id) {
                $currentTenant = $user->tenant;
            }

            if (!$currentTenant) {
                throw ValidationException::withMessages([
                    'email' => ['No tenant context found. Please access through a valid tenant domain.'],
                ]);
            }

            if ($user->tenant_id !== $currentTenant->id) {
                throw ValidationException::withMessages([
                    'email' => ['You do not have access to this tenant.'],
                ]);
            }

            // Check if tenant is active
            if (!$currentTenant->is_active) {
                throw ValidationException::withMessages([
                    'email' => ['This tenant account is inactive.'],
                ]);
            }

            // Check tenant license
            if (!$currentTenant->hasValidLicense()) {
                throw ValidationException::withMessages([
                    'email' => ['Tenant license has expired. Please contact your administrator.'],
                ]);
            }
        }
        // Super admins can login without tenant context

        // Check if account is locked
        if ($user->isAccountLocked()) {
            throw ValidationException::withMessages([
                'email' => ['Account is temporarily locked due to multiple failed attempts. Please try again later.'],
            ]);
        }

        // Check for too many failed attempts
        if ($user->shouldLockAccount()) {
            $user->lockAccount();
            throw ValidationException::withMessages([
                'email' => ['Too many failed attempts. Account has been locked for 30 minutes.'],
            ]);
        }

        // Check if user has two factor authentication enabled
        if ($user->hasTwoFactorAuthenticationEnabled()) {
            // Store user ID in session for 2FA challenge
            session([
                '2fa_user_id' => $user->id,
                '2fa_remember' => $request->boolean('remember')
            ]);

            // Send 2FA code if method is SMS or email
            if ($user->two_factor_method === 'sms') {
                $user->sendTwoFactorCodeViaSms();
            } elseif ($user->two_factor_method === 'email') {
                $user->sendTwoFactorCodeViaEmail();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'two_factor_required' => true,
                    'two_factor_method' => $user->two_factor_method,
                    'message' => 'Two factor authentication required'
                ]);
            }

            return redirect()->route('two-factor.challenge');
        }

        // Record successful login
        $user->recordLoginAttempt(request()->ip(), true);

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        if ($request->expectsJson()) {
            return response()->json([
                'user' => $user->load(['roles', 'permissions', 'tenant']),
                'token' => $token,
                'tenant' => $user->isSuperAdmin() ? null : $user->tenant,
            ]);
        }

        // Login the user for web requests
        auth()->guard('web')->login($user, $request->boolean('remember'));
        return redirect()->intended('/dashboard');
    }

    /**
     * Register new user (tenant users only)
     */
    public function register(Request $request)
    {
        $currentTenant = Tenant::current();

        if (!$currentTenant) {
            return response()->json([
                'message' => 'No tenant context found. Registration not allowed.'
            ], 403);
        }

        if (!$currentTenant->canAddUser()) {
            return response()->json([
                'message' => 'Maximum user limit reached for this tenant.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'department' => $request->department,
            'position' => $request->position,
            'tenant_id' => $currentTenant->id,
            'is_active' => true,
        ]);

        // Assign default role (you might want to make this configurable)
        $user->assignRole('cashier');

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load(['roles', 'permissions', 'tenant']),
            'token' => $token,
            'tenant' => $user->tenant,
        ], 201);
    }



    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user()->load(['roles', 'permissions', 'tenant']);

        return response()->json([
            'user' => $user,
            'tenant' => $user->tenant,
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        $user->update($request->only(['name', 'phone', 'department', 'position']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()->load(['roles', 'permissions', 'tenant'])
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logged out successfully'
            ]);
        }

        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'تم تسجيل الخروج بنجاح');
    }
}
