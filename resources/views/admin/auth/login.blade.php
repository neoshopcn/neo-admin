<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>后台登录</title>
    @php
        $adminThemeKeys = array_keys(config('admin.themes'));
        $adminDefaultTheme = config('admin.default_theme');
    @endphp
    <script>
        (function () {
            var def = @json($adminDefaultTheme);
            var keys = @json($adminThemeKeys);
            try {
                var t = localStorage.getItem('neo_admin_theme');
                if (t && keys.indexOf(t) !== -1) def = t;
            } catch (e) {}
            document.documentElement.setAttribute('data-login-theme', def);
        })();
    </script>
    <style>
        * { box-sizing: border-box; }
        @foreach (config('admin.themes') as $key => $theme)
        :root[data-login-theme="{{ $key }}"] {
            --login-accent: {{ $theme['primary'] }};
        }
        @endforeach
        :root[data-login-theme="sky"] {
            --login-bg-deep: #062c42;
            --login-bg-mid: #0b4668;
            --login-bg-end: #041f30;
        }
        :root {
            --login-accent: #409EFF;
            --login-bg-deep: #0f141c;
            --login-bg-mid: #151c28;
            --login-bg-end: #0c1018;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            font-family: system-ui,-apple-system,"PingFang SC","Microsoft YaHei",sans-serif;
            background: var(--login-bg-deep);
        }

        .login-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
            background: var(--login-bg-deep);
        }
        .login-bg__base {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 120% 80% at 50% -10%, color-mix(in srgb, var(--login-accent) 14%, transparent), transparent 55%),
                radial-gradient(ellipse 90% 70% at 100% 60%, color-mix(in srgb, var(--login-accent) 8%, transparent), transparent 50%),
                radial-gradient(ellipse 80% 60% at 0% 80%, color-mix(in srgb, var(--login-accent) 6%, transparent), transparent 48%),
                linear-gradient(165deg, var(--login-bg-deep) 0%, var(--login-bg-mid) 45%, var(--login-bg-end) 100%);
        }
        .login-bg__veil {
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 85% 75% at 50% 45%, transparent 0%, rgba(0, 0, 0, 0.45) 100%);
            opacity: 0.85;
        }
        .login-bg__gradient {
            position: absolute;
            inset: -15%;
            background:
                radial-gradient(ellipse 50% 40% at 18% 22%, color-mix(in srgb, var(--login-accent) 18%, transparent), transparent 58%),
                radial-gradient(ellipse 45% 38% at 82% 72%, color-mix(in srgb, var(--login-accent) 12%, transparent), transparent 55%);
            opacity: 0.55;
            animation: loginBgDrift 32s ease-in-out infinite alternate;
        }
        .login-bg__blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(88px);
            will-change: transform;
            background: color-mix(in srgb, var(--login-accent) 22%, var(--login-bg-end));
            opacity: 0.35;
        }
        .login-bg__blob--1 {
            width: min(58vw, 480px);
            height: min(58vw, 480px);
            top: -18%;
            left: -12%;
            animation: loginBlobA 24s ease-in-out infinite;
        }
        .login-bg__blob--2 {
            width: min(52vw, 420px);
            height: min(52vw, 420px);
            bottom: -22%;
            right: -14%;
            opacity: 0.28;
            animation: loginBlobB 28s ease-in-out infinite;
        }
        .login-bg__blob--3 {
            width: min(40vw, 320px);
            height: min(40vw, 320px);
            top: 38%;
            left: 46%;
            opacity: 0.2;
            animation: loginBlobC 34s ease-in-out infinite;
        }

        .login-bg__dots {
            position: absolute;
            inset: 0;
        }
        .login-bg__dot {
            position: absolute;
            width: var(--dot-size, 4px);
            height: var(--dot-size, 4px);
            margin-left: calc(var(--dot-size, 4px) / -2);
            margin-top: calc(var(--dot-size, 4px) / -2);
            border-radius: 50%;
            background: radial-gradient(circle at 32% 28%, rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.35) 38%, rgba(255, 255, 255, 0.08) 62%, transparent 78%);
            box-shadow:
                0 0 calc(var(--dot-glow, 14px) * 0.55) rgba(255, 255, 255, 0.55),
                0 0 var(--dot-glow, 14px) color-mix(in srgb, var(--login-accent) 48%, rgba(220, 235, 255, 0.55));
            animation-duration: var(--dot-dur, 18s);
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
            animation-delay: var(--dot-delay, 0s);
        }
        .login-bg__dot--m0 { animation-name: loginDotDrift0; }
        .login-bg__dot--m1 { animation-name: loginDotDrift1; }
        .login-bg__dot--m2 { animation-name: loginDotDrift2; }
        .login-bg__dot--m3 { animation-name: loginDotDrift3; }

        @keyframes loginBgDrift {
            0% { transform: translate(0, 0) rotate(0deg) scale(1); opacity: 0.5; }
            100% { transform: translate(1.5%, -2%) rotate(-3deg) scale(1.06); opacity: 0.62; }
        }
        @keyframes loginBlobA {
            0%, 100% { transform: translate(0, 0) scale(1); }
            40% { transform: translate(4%, 3%) scale(1.05); }
            70% { transform: translate(-3%, 2%) scale(0.98); }
        }
        @keyframes loginBlobB {
            0%, 100% { transform: translate(0, 0) scale(1); }
            45% { transform: translate(-4%, -3%) scale(1.04); }
            75% { transform: translate(3%, -2%) scale(0.99); }
        }
        @keyframes loginBlobC {
            0%, 100% { transform: translate(0, 0) scale(1); }
            45% { transform: translate(8%, -10%) scale(1.06); }
            80% { transform: translate(-5%, 6%) scale(0.97); }
        }
        @keyframes loginDotDrift0 {
            0%, 100% { transform: translate(0, 0); opacity: 0.42; }
            35% { transform: translate(14px, -26px); opacity: 0.88; }
            70% { transform: translate(-10px, 16px); opacity: 0.58; }
        }
        @keyframes loginDotDrift1 {
            0%, 100% { transform: translate(0, 0); opacity: 0.38; }
            40% { transform: translate(-18px, -14px); opacity: 0.82; }
            75% { transform: translate(12px, 20px); opacity: 0.52; }
        }
        @keyframes loginDotDrift2 {
            0%, 100% { transform: translate(0, 0); opacity: 0.48; }
            45% { transform: translate(10px, 22px); opacity: 0.9; }
            80% { transform: translate(-16px, -10px); opacity: 0.45; }
        }
        @keyframes loginDotDrift3 {
            0%, 100% { transform: translate(0, 0); opacity: 0.4; }
            50% { transform: translate(-12px, 24px); opacity: 0.78; }
            85% { transform: translate(18px, -8px); opacity: 0.55; }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-bg__gradient,
            .login-bg__blob { animation: none; }
            .login-bg__dot {
                animation: none;
                opacity: 0.55;
            }
        }

        .card {
            position: relative;
            z-index: 1;
            width: 400px;
            max-width: 92vw;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-radius: 12px;
            box-shadow:
                0 4px 24px rgba(0, 0, 0, 0.35),
                0 24px 56px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.08) inset;
            padding: 28px 28px 22px;
        }
        .title { text-align: center; font-size: 20px; font-weight: 600; color: #303133; margin-bottom: 22px; }
        label { display: block; font-size: 13px; color: #606266; margin-bottom: 6px; }
        .field { margin-bottom: 16px; }
        input[type=text], input[type=password] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dcdfe6;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color .15s;
            background: rgba(255, 255, 255, 0.9);
        }
        input:focus { border-color: var(--login-accent); }
        .cap { display: flex; gap: 10px; align-items: center; }
        .cap input { flex: 1; }
        .cap img { height: 40px; border-radius: 4px; cursor: pointer; border: 1px solid #ebeef5; }
        .row { display: flex; align-items: center; gap: 8px; margin-bottom: 18px; font-size: 14px; color: #606266; }
        button {
            width: 100%;
            padding: 11px;
            border: none;
            border-radius: 6px;
            background: var(--login-accent);
            color: #fff;
            font-size: 15px;
            cursor: pointer;
            transition: filter .15s, transform .12s;
        }
        button:hover { filter: brightness(1.08); }
        button:active { transform: scale(0.99); }
        .err { background: #fef0f0; color: #f56c6c; padding: 10px 12px; border-radius: 6px; font-size: 13px; margin-bottom: 14px; }
        .login-footer {
            margin: 22px 0 0;
            padding-top: 18px;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            text-align: center;
            font-size: 12px;
            line-height: 1.5;
            color: #909399;
            letter-spacing: 0.03em;
            user-select: none;
        }
    </style>
</head>
<body>
<div class="login-bg" aria-hidden="true">
    <div class="login-bg__base"></div>
    <div class="login-bg__veil"></div>
    <div class="login-bg__gradient"></div>
    <div class="login-bg__blob login-bg__blob--1"></div>
    <div class="login-bg__blob login-bg__blob--2"></div>
    <div class="login-bg__blob login-bg__blob--3"></div>
    <div class="login-bg__dots">
        @for ($i = 0; $i < 52; $i++)
            @php
                $left = (($i * 47 + 19) % 96) + 2;
                $top = (($i * 29 + 37) % 92) + 4;
                $dur = 16 + ($i % 13);
                $delay = fmod($i * 1.73, 22);
                $size = 3.2 + ($i % 5) * 1.05;
                $glow = 14 + ($i % 8) * 2;
                $mod = $i % 4;
            @endphp
            <span class="login-bg__dot login-bg__dot--m{{ $mod }}" style="left: {{ $left }}%; top: {{ $top }}%; --dot-dur: {{ $dur }}s; --dot-delay: -{{ number_format($delay, 2, '.', '') }}s; --dot-size: {{ number_format($size, 2, '.', '') }}px; --dot-glow: {{ $glow }}px;"></span>
        @endfor
    </div>
</div>
<div class="card">
    <div class="title">NeoAdmin 登录</div>
    @if ($errors->any())
        <div class="err">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('admin.login') }}">
        @csrf
        <div class="field">
            <label for="username">账号</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" required autocomplete="username" placeholder="用户名">
        </div>
        <div class="field">
            <label for="password">密码</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="密码">
        </div>
        <div class="field">
            <label for="captcha">验证码</label>
            <div class="cap">
                <input id="captcha" name="captcha" type="text" maxlength="10" required placeholder="验证码">
                <img src="{{ route('admin.captcha') }}" alt="验证码" title="点击刷新" onclick="this.src='{{ route('admin.captcha') }}?t='+Date.now()">
            </div>
        </div>
        <div class="row">
            <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember" style="margin:0">记住我</label>
        </div>
        <button type="submit">登录</button>
    </form>
    @if (config('admin.copyright_footer_enabled', true))
        <p class="login-footer" role="contentinfo">{{ config('admin.copyright_footer_name', 'NeoAdmin') }} © 2025–{{ date('Y') }}</p>
    @endif
</div>
</body>
</html>
