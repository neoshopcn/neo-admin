<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>双因子验证</title>
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
            pointer-events: none;
            background:
                radial-gradient(ellipse 120% 80% at 50% -10%, color-mix(in srgb, var(--login-accent) 14%, transparent), transparent 55%),
                linear-gradient(165deg, var(--login-bg-deep) 0%, var(--login-bg-mid) 45%, var(--login-bg-end) 100%);
        }
        .card {
            position: relative;
            z-index: 1;
            width: 420px;
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
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            color: #303133;
            margin-bottom: 16px;
        }
        .tip {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            margin-bottom: 22px;
            background: linear-gradient(135deg, #ecf5ff 0%, #f4faff 100%);
            border: 1px solid #d9ecff;
            border-radius: 10px;
            color: #606266;
            font-size: 13px;
            line-height: 1.65;
        }
        .tip__icon {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            margin-top: 1px;
            color: var(--login-accent);
        }
        .tip__icon svg { display: block; width: 22px; height: 22px; }
        .field { margin-bottom: 22px; }
        .otp-row {
            display: flex;
            justify-content: center;
            gap: 8px;
            max-width: 100%;
        }
        .otp-box {
            width: 54px;
            height: 54px;
            padding: 0;
            border: 1px solid #dcdfe6;
            border-radius: 11px;
            background: rgba(255, 255, 255, 0.95);
            font-size: 24px;
            font-weight: 600;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            text-align: center;
            color: #303133;
            outline: none;
            transition: border-color .15s, box-shadow .15s, transform .12s;
            caret-color: var(--login-accent);
        }
        .otp-box:focus {
            border-color: var(--login-accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--login-accent) 22%, transparent);
            transform: translateY(-1px);
        }
        .otp-box.filled {
            border-color: color-mix(in srgb, var(--login-accent) 55%, #dcdfe6);
            background: #fafcff;
        }
        .otp-box.error {
            border-color: #f56c6c;
            animation: otp-shake .35s ease;
        }
        @keyframes otp-shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }
        @media (max-width: 400px) {
            .otp-row { gap: 6px; }
            .otp-box { width: 46px; height: 46px; font-size: 22px; border-radius: 9px; }
        }
        button[type=submit] {
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
        button[type=submit]:hover { filter: brightness(1.08); }
        button[type=submit]:active { transform: scale(0.99); }
        button[type=submit]:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            filter: none;
        }
        .err {
            background: #fef0f0;
            color: #f56c6c;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 14px;
        }
        .back {
            display: block;
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: #909399;
            text-decoration: none;
        }
        .back:hover { color: var(--login-accent); }
    </style>
</head>
<body>
<div class="login-bg" aria-hidden="true"></div>
<div class="card">
    <div class="title">双因子验证</div>
    <div class="tip">
        <span class="tip__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="7" y="2" width="10" height="20" rx="2.5" stroke="currentColor" stroke-width="1.6"/>
                <circle cx="12" cy="18" r="1.2" fill="currentColor"/>
                <path d="M9 5.5h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                <path d="M10 8.5h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" opacity="0.75"/>
            </svg>
        </span>
        <span>请打开 Google Authenticator 或其他验证器应用，输入 6 位动态验证码</span>
    </div>
    @if ($errors->any())
        <div class="err">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('admin.login.2fa.post') }}" id="otpForm">
        @csrf
        <div class="field">
            {{-- <label for="code">验证码</label> --}}
            <input type="hidden" id="code" name="code" value="{{ old('code') }}" required>
            <div class="otp-row" id="otpRow">
                @for ($i = 0; $i < 6; $i++)
                    <input class="otp-box" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}" aria-label="验证码第 {{ $i + 1 }} 位" data-idx="{{ $i }}">
                @endfor
            </div>
        </div>
        <button type="submit" id="submitBtn" disabled>验证并登录</button>
    </form>
    <a class="back" href="{{ route('admin.login') }}">返回登录</a>
</div>
<script>
(function () {
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
    var hidden = document.getElementById('code');
    var form = document.getElementById('otpForm');
    var submitBtn = document.getElementById('submitBtn');

    function onlyDigit(v) {
        return (v || '').replace(/\D/g, '');
    }

    function sync() {
        var val = boxes.map(function (b) { return b.value; }).join('');
        hidden.value = val;
        submitBtn.disabled = val.length !== 6;
        boxes.forEach(function (b) {
            b.classList.toggle('filled', b.value.length > 0);
        });
    }

    function fillFromString(str, focusEnd) {
        var digits = onlyDigit(str).slice(0, 6);
        boxes.forEach(function (b, i) {
            b.value = digits[i] || '';
        });
        sync();
        if (focusEnd !== false) {
            var idx = Math.min(digits.length, 5);
            boxes[idx].focus();
        }
    }

    boxes.forEach(function (box, i) {
        box.addEventListener('input', function (e) {
            var v = onlyDigit(e.target.value);
            if (v.length > 1) {
                fillFromString(v);
                return;
            }
            e.target.value = v;
            if (v && i < boxes.length - 1) {
                boxes[i + 1].focus();
            }
            sync();
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') {
                if (!box.value && i > 0) {
                    boxes[i - 1].focus();
                    boxes[i - 1].value = '';
                    sync();
                    e.preventDefault();
                }
                return;
            }
            if (e.key === 'ArrowLeft' && i > 0) {
                boxes[i - 1].focus();
                e.preventDefault();
            }
            if (e.key === 'ArrowRight' && i < boxes.length - 1) {
                boxes[i + 1].focus();
                e.preventDefault();
            }
        });

        box.addEventListener('focus', function () {
            box.select();
        });

        box.addEventListener('paste', function (e) {
            e.preventDefault();
            fillFromString(e.clipboardData.getData('text') || '');
        });
    });

    form.addEventListener('submit', function (e) {
        sync();
        if (hidden.value.length !== 6) {
            e.preventDefault();
            boxes.forEach(function (b) {
                b.classList.add('error');
            });
            setTimeout(function () {
                boxes.forEach(function (b) { b.classList.remove('error'); });
            }, 400);
            boxes[0].focus();
        }
    });

    if (hidden.value && onlyDigit(hidden.value).length === 6) {
        fillFromString(hidden.value, false);
    } else {
        boxes[0].focus();
    }
    sync();
})();
</script>
</body>
</html>
