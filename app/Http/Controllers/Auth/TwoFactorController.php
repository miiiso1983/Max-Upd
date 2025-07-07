<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorController extends Controller
{
    /**
     * Show two factor authentication setup page
     */
    public function show()
    {
        $user = Auth::user();
        
        return view('auth.two-factor', [
            'user' => $user,
            'qrCode' => $user->two_factor_enabled ? $this->generateQrCode($user) : null,
            'recoveryCodes' => $user->two_factor_enabled ? $user->getRecoveryCodes() : [],
        ]);
    }
    
    /**
     * Enable two factor authentication
     */
    public function enable(Request $request)
    {
        $request->validate([
            'method' => 'required|in:app,sms,email',
            'phone' => 'required_if:method,sms|nullable|string',
        ]);
        
        $user = Auth::user();
        
        if ($user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'المصادقة الثنائية مفعلة بالفعل'
            ], 400);
        }
        
        $secretKey = $user->enableTwoFactorAuthentication($request->method);
        
        if ($request->method === 'sms') {
            $user->update(['two_factor_phone' => $request->phone]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'تم تفعيل المصادقة الثنائية بنجاح',
            'secret_key' => $secretKey,
            'qr_code' => $this->generateQrCode($user),
            'recovery_codes' => $user->getRecoveryCodes(),
        ]);
    }
    
    /**
     * Confirm two factor authentication setup
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);
        
        $user = Auth::user();
        
        if (!$user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'المصادقة الثنائية غير مفعلة'
            ], 400);
        }
        
        if ($user->two_factor_confirmed_at) {
            return response()->json([
                'success' => false,
                'message' => 'المصادقة الثنائية مؤكدة بالفعل'
            ], 400);
        }
        
        $verified = false;
        
        switch ($user->two_factor_method) {
            case 'app':
                $verified = $user->verifyTwoFactorCode($request->code);
                break;
            case 'sms':
                $verified = $user->verifySmsCode($request->code);
                break;
            case 'email':
                $verified = $user->verifyEmailCode($request->code);
                break;
        }
        
        if ($verified) {
            $user->update(['two_factor_confirmed_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => 'تم تأكيد المصادقة الثنائية بنجاح'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'الرمز غير صحيح'
        ], 400);
    }
    
    /**
     * Disable two factor authentication
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'code' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        if (!$user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'المصادقة الثنائية غير مفعلة'
            ], 400);
        }
        
        // Verify password
        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'كلمة المرور غير صحيحة'
            ], 400);
        }
        
        // Verify 2FA code or recovery code
        $verified = $user->verifyTwoFactorCode($request->code) || 
                   $user->verifyRecoveryCode($request->code);
        
        if (!$verified) {
            return response()->json([
                'success' => false,
                'message' => 'الرمز غير صحيح'
            ], 400);
        }
        
        $user->disableTwoFactorAuthentication();
        
        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء تفعيل المصادقة الثنائية بنجاح'
        ]);
    }
    
    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);
        
        $user = Auth::user();
        
        if (!$user->hasTwoFactorAuthenticationEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'المصادقة الثنائية غير مفعلة أو غير مؤكدة'
            ], 400);
        }
        
        // Verify 2FA code
        if (!$user->verifyTwoFactorCode($request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'الرمز غير صحيح'
            ], 400);
        }
        
        $recoveryCodes = $user->regenerateRecoveryCodes();
        
        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء رموز الاسترداد الجديدة بنجاح',
            'recovery_codes' => $recoveryCodes,
        ]);
    }
    
    /**
     * Send 2FA code via SMS or email
     */
    public function sendCode(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasTwoFactorAuthenticationEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'المصادقة الثنائية غير مفعلة'
            ], 400);
        }
        
        $sent = false;
        
        switch ($user->two_factor_method) {
            case 'sms':
                $sent = $user->sendTwoFactorCodeViaSms();
                break;
            case 'email':
                $sent = $user->sendTwoFactorCodeViaEmail();
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'طريقة الإرسال غير مدعومة'
                ], 400);
        }
        
        if ($sent) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الرمز بنجاح'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'فشل في إرسال الرمز'
        ], 500);
    }
    
    /**
     * Show two factor challenge page
     */
    public function challenge()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }
        
        $user = \App\Models\User::find(session('2fa_user_id'));
        
        return view('auth.two-factor-challenge', [
            'user' => $user,
        ]);
    }
    
    /**
     * Verify two factor challenge
     */
    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);
        
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }
        
        $user = \App\Models\User::find(session('2fa_user_id'));
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $verified = false;
        
        // Try to verify as 2FA code first
        switch ($user->two_factor_method) {
            case 'app':
                $verified = $user->verifyTwoFactorCode($request->code);
                break;
            case 'sms':
                $verified = $user->verifySmsCode($request->code);
                break;
            case 'email':
                $verified = $user->verifyEmailCode($request->code);
                break;
        }
        
        // If not verified, try as recovery code
        if (!$verified) {
            $verified = $user->verifyRecoveryCode($request->code);
        }
        
        if ($verified) {
            // Clear 2FA session
            session()->forget('2fa_user_id');

            // Log the user in
            Auth::login($user, session('2fa_remember', false));
            session()->forget('2fa_remember');

            // Mark 2FA as verified for this session
            session(['2fa_verified_' . $user->id => true]);

            // Record successful login
            $user->recordLoginAttempt(request()->ip(), true);

            return redirect()->intended('/dashboard');
        }
        
        // Record failed attempt
        $user->recordLoginAttempt(request()->ip(), false);
        
        return back()->withErrors([
            'code' => 'الرمز غير صحيح'
        ]);
    }
    
    /**
     * Generate QR code for Google Authenticator
     */
    private function generateQrCode($user): string
    {
        $qrCodeUrl = $user->getTwoFactorQrCodeUrl();
        
        return QrCode::size(200)->generate($qrCodeUrl);
    }
}
