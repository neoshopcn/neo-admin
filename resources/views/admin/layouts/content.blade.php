<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '后台')</title>
    <script>
        (function () {
            var primaries = @json(collect(config('admin.themes'))->map(fn ($t) => $t['primary'])->all());
            var def = @json(config('admin.default_theme'));
            var key = def;
            try {
                var saved = localStorage.getItem('neo_admin_theme');
                if (saved && primaries[saved]) key = saved;
            } catch (e) {}
            var p = primaries[key] || primaries[def] || '#409EFF';
            document.documentElement.style.setProperty('--el-color-primary', p);
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('assets/admin-static/element-plus/index.css') }}"/>
    <style>
        body { margin: 0; background: #f5f7fa; font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "PingFang SC", "Microsoft YaHei", sans-serif; }
        .neo-wrap { padding: 16px; min-height: 100vh; box-sizing: border-box; }
    </style>
</head>
<body>
<div id="app" class="neo-wrap"></div>
<script>
    window.__ADMIN_PERM_CODES__ = @json(\App\Support\AdminPermission::sessionCodes());
    window.neoAxiosSetup = function (axios) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.withCredentials = true;
        axios.interceptors.response.use(
            (r) => r,
            (err) => {
                if (err.response && err.response.status === 401) {
                    const target = (window.top && window.top.location) ? window.top : window;
                    target.location.href = @json(url('/admin/login'));
                }
                return Promise.reject(err);
            }
        );
    };
</script>
<script src="{{ asset('assets/admin-static/vue/vue.global.js') }}"></script>
<script src="{{ asset('assets/admin-static/axios/axios.min.js') }}"></script>
<script src="{{ asset('assets/admin-static/element-plus/index.full.min.js') }}"></script>
<script src="{{ asset('assets/admin-static/element-plus/locale/zh-cn.min.js') }}"></script>
<script src="{{ asset('assets/admin-static/element-plus-icons/index.iife.min.js') }}"></script>
@stack('scripts')
</body>
</html>
