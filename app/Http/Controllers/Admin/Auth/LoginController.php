<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminLoginLog;
use App\Services\CaptchaService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.home');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request, CaptchaService $captcha): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:64'],
            'password' => ['required', 'string'],
            'captcha' => ['required', 'string', 'max:16'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        if (! $captcha->validate($credentials['captcha'])) {
            return back()->withInput($request->except('password'))->withErrors(['captcha' => '验证码错误或已过期']);
        }

        if (! Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], (bool) $request->boolean('remember'))) {
            AdminLoginLog::query()->create([
                'user_id' => null,
                'username' => $credentials['username'],
                'ip' => $request->ip(),
                'user_agent' => self::truncateUa($request->userAgent()),
                'success' => false,
                'created_at' => now(),
            ]);

            return back()->withInput($request->except('password'))->withErrors(['username' => '账号或密码错误']);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if ((int) $user->status !== 1) {
            Auth::logout();
            AdminLoginLog::query()->create([
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'user_agent' => self::truncateUa($request->userAgent()),
                'success' => false,
                'created_at' => now(),
            ]);

            return back()->withInput($request->except('password'))->withErrors(['username' => '账号已禁用']);
        }

        $user->load('roles');
        AdminPermission::refreshSession($user);

        AdminLoginLog::query()->create([
            'user_id' => $user->id,
            'username' => $user->username,
            'ip' => $request->ip(),
            'user_agent' => self::truncateUa($request->userAgent()),
            'success' => true,
            'created_at' => now(),
        ]);

        return redirect()->intended(route('admin.home'));
    }

    private static function truncateUa(?string $ua): ?string
    {
        if ($ua === null || $ua === '') {
            return null;
        }
        if (strlen($ua) <= 2000) {
            return $ua;
        }

        return substr($ua, 0, 2000);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
