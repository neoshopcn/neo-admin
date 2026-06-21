<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminLoginLog;
use App\Models\User;
use App\Services\CaptchaService;
use App\Services\Google2faService;
use App\Support\AdminPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const SESSION_PENDING = 'google2fa_pending';

    private const SESSION_ATTEMPTS = 'google2fa_attempts';

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

        if (! Auth::validate([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ])) {
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

        /** @var User $user */
        $user = User::query()->where('username', $credentials['username'])->firstOrFail();

        if ((int) $user->status !== 1) {
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

        if ($user->isGoogle2faEnabled()) {
            $google2fa = app(Google2faService::class);

            if ($google2fa->isLocked($user)) {
                AdminLoginLog::query()->create([
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'ip' => $request->ip(),
                    'user_agent' => self::truncateUa($request->userAgent()),
                    'success' => false,
                    'created_at' => now(),
                ]);

                return back()->withInput($request->except('password'))->withErrors([
                    'username' => '双因子验证已锁定，请稍后再试或联系管理员解除锁定',
                ]);
            }

            $request->session()->put(self::SESSION_PENDING, [
                'user_id' => $user->id,
                'remember' => $request->boolean('remember'),
            ]);
            $request->session()->put(self::SESSION_ATTEMPTS, 0);

            return redirect()->route('admin.login.2fa');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->finishLogin($request, $user);
    }

    public function showTwoFactorForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.home');
        }

        if (! $request->session()->has(self::SESSION_PENDING)) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.login-2fa');
    }

    public function verifyTwoFactor(Request $request, Google2faService $google2fa): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pending = $request->session()->get(self::SESSION_PENDING);
        if (! is_array($pending) || empty($pending['user_id'])) {
            return redirect()->route('admin.login')->withErrors(['username' => '登录会话已过期，请重新登录']);
        }

        $user = User::query()->find((int) $pending['user_id']);
        if (! $user || (int) $user->status !== 1 || ! $user->isGoogle2faEnabled()) {
            $request->session()->forget([self::SESSION_PENDING, self::SESSION_ATTEMPTS]);

            return redirect()->route('admin.login')->withErrors(['username' => '登录会话无效，请重新登录']);
        }

        if ($google2fa->isLocked($user)) {
            $request->session()->forget([self::SESSION_PENDING, self::SESSION_ATTEMPTS]);

            return redirect()->route('admin.login')->withErrors([
                'username' => '双因子验证已锁定，请稍后再试或联系管理员解除锁定',
            ]);
        }

        if (! $google2fa->verify($user, $data['code'])) {
            $attempts = (int) $request->session()->get(self::SESSION_ATTEMPTS, 0) + 1;
            $request->session()->put(self::SESSION_ATTEMPTS, $attempts);

            if ($attempts >= $google2fa->maxAttempts()) {
                $google2fa->lock($user);
                $request->session()->forget([self::SESSION_PENDING, self::SESSION_ATTEMPTS]);

                AdminLoginLog::query()->create([
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'ip' => $request->ip(),
                    'user_agent' => self::truncateUa($request->userAgent()),
                    'success' => false,
                    'created_at' => now(),
                ]);

                return redirect()->route('admin.login')->withErrors([
                    'username' => '双因子验证失败次数过多，账号已临时锁定',
                ]);
            }

            return back()->withErrors(['code' => '验证码错误，还可尝试 '.($google2fa->maxAttempts() - $attempts).' 次']);
        }

        $remember = ! empty($pending['remember']);
        $request->session()->forget([self::SESSION_PENDING, self::SESSION_ATTEMPTS]);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return $this->finishLogin($request, $user);
    }

    private function finishLogin(Request $request, User $user): RedirectResponse
    {
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
