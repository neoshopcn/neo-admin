<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('admin.title', '后台管理') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/admin-static/element-plus/index.css') }}"/>
    <style>
        html, body { height: 100%; margin: 0; }
        body { font-family: system-ui,-apple-system,"PingFang SC","Microsoft YaHei",sans-serif; background: #f5f7fa; }
        .logo {
            height: 56px; display: flex; align-items: center; justify-content: flex-start; gap: 8px;
            padding: 0 16px 0 20px;
            box-sizing: border-box;
            font-weight: 700; letter-spacing: .5px; font-size: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .logo img {
            flex-shrink: 0; height: 28px; width: auto; max-width: 100%; object-fit: contain;
        }
        .logo.logo--collapsed {
            justify-content: center;
            padding: 0 6px;
        }
        .logo.logo--collapsed img { height: 24px; }
        .el-aside { border-right: 1px solid rgba(0,0,0,0.12); overflow: hidden; }
        .el-aside .el-menu { border-right: none; }
        .el-aside .el-menu-item:hover,
        .el-aside .el-menu-item:focus,
        .el-aside .el-sub-menu__title:hover {
            background-color: var(--neo-sidebar-menu-hover-bg, transparent) !important;
            color: var(--neo-sidebar-menu-hover-color, inherit) !important;
        }
        .el-aside .el-menu-item:hover .el-icon,
        .el-aside .el-sub-menu__title:hover .el-icon {
            color: var(--neo-sidebar-menu-hover-color, inherit) !important;
        }
        .shell-main-wrap { flex: 1; min-width: 0; min-height: 0; display: flex; flex-direction: column; }
        .shell-head { flex-shrink: 0; background: #fff; border-bottom: 1px solid #ebeef5; }
        .shell-head-row {
            height: 56px; display: flex; align-items: center; justify-content: space-between;
            gap: 12px; padding: 0 16px; box-sizing: border-box;
        }
        .shell-head-left { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
        .shell-head-left .el-breadcrumb { font-size: 14px; min-width: 0; }
        .shell-head-left .el-breadcrumb__inner { max-width: 42vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .shell-head-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .shell-hist-row {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 8px 16px 10px; background: #fafafa; border-bottom: 1px solid #ebeef5;
        }
        .hist-label { color: #909399; font-size: 13px; white-space: nowrap; padding-top: 4px; }
        .hist-tags { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; flex: 1; min-width: 0; }
        .hist-tags .el-tag { cursor: pointer; }
        .user-avatar-hit { cursor: pointer; display: inline-flex; border-radius: 50%; padding: 2px; line-height: 0; }
        .user-avatar-hit:hover { background: #f2f6fc; }
        .user-pop-title { font-size: 14px; color: #303133; line-height: 1.5; margin-bottom: 12px; }
        .user-pop-title .muted { color: #909399; font-size: 13px; }
        .user-pop-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .collapse-hit { cursor: pointer; padding: 6px; border-radius: 6px; display: inline-flex; align-items: center; }
        .collapse-hit:hover { background: #f2f6fc; }
        iframe { background: #f5f7fa; }
    </style>
</head>
<body>
<div id="shell">
    <el-container style="height:100vh">
        <el-aside :width="asideW" :style="{ background: activeTheme.sidebar_bg }">
            <div class="logo" v-if="!collapsed" :style="{ background: activeTheme.sidebar_logo_bg, color: activeTheme.sidebar_text }">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('admin.brand_name', 'NeoAdmin') }}" />
                <span>{{ config('admin.brand_name', 'NeoAdmin') }}</span>
            </div>
            <div class="logo logo--collapsed" v-else :style="{ background: activeTheme.sidebar_logo_bg, color: activeTheme.sidebar_text }">
                <img src="{{ asset('images/logo.png') }}" alt="" />
            </div>
            <el-scrollbar style="height:calc(100vh - 56px)">
                <el-menu
                    :collapse="collapsed"
                    :default-active="activePath"
                    :router="false"
                    :background-color="activeTheme.sidebar_bg"
                    :text-color="activeTheme.sidebar_text"
                    :active-text-color="activeTheme.sidebar_active"
                    @select="onSelect"
                >
                    <template v-for="m in menuTree" :key="m.id">
                        <el-sub-menu v-if="m.type === 0 && m.children && m.children.length" :index="'dir_'+m.id">
                            <template #title>
                                <el-icon><component :is="icon(m.icon)"/></el-icon>
                                <span>@{{ m.name }}</span>
                            </template>
                            <el-menu-item v-for="c in m.children" :key="c.id" :index="c.path">
                                <el-icon><component :is="icon(c.icon)"/></el-icon>
                                <span>@{{ c.name }}</span>
                            </el-menu-item>
                        </el-sub-menu>
                        <el-menu-item v-else-if="m.type === 1" :index="m.path">
                            <el-icon><component :is="icon(m.icon)"/></el-icon>
                            <span>@{{ m.name }}</span>
                        </el-menu-item>
                    </template>
                </el-menu>
            </el-scrollbar>
        </el-aside>

        <el-container direction="vertical" class="shell-main-wrap">
            <div class="shell-head">
                <div class="shell-head-row">
                    <div class="shell-head-left">
                        <span class="collapse-hit" @click="toggleCollapse" title="收起/展开">
                            <el-icon size="20"><component :is="collapsed ? 'Expand' : 'Fold'"/></el-icon>
                        </span>
                        <el-breadcrumb separator="/">
                            <el-breadcrumb-item v-for="(seg, i) in breadcrumbItems" :key="i">@{{ seg }}</el-breadcrumb-item>
                        </el-breadcrumb>
                    </div>
                    <div class="shell-head-right">
                        <el-dropdown trigger="click" @command="setTheme">
                            <span class="collapse-hit" title="主题色">
                                <el-icon size="20"><Brush /></el-icon>
                            </span>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item v-for="(meta, key) in themes" :key="key" :command="key" :disabled="key === themeKey">
                                        @{{ meta.label }}
                                    </el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                        <span class="collapse-hit" @click="refreshContent" title="刷新当前页">
                            <el-icon size="20"><Refresh /></el-icon>
                        </span>
                        <el-popover placement="bottom-end" :width="268" trigger="hover">
                            <template #reference>
                                <span class="user-avatar-hit" title="账户菜单">
                                    <el-avatar :size="36" :src="user.avatar_url || ''">
                                        <span style="font-size:14px">@{{ userInitial }}</span>
                                    </el-avatar>
                                </span>
                            </template>
                            <div>
                                <div class="user-pop-title">
                                    @{{ user.name || user.username }}
                                    <span class="muted">（@{{ user.role_name || '未分配角色' }}）</span>
                                </div>
                                <div class="user-pop-actions">
                                    <el-button size="small" type="primary" plain @click="openProfile">个人信息</el-button>
                                    <form method="post" action="{{ route('admin.logout') }}" style="margin:0" @submit="clearRecentHistoryStorage">
                                        @csrf
                                        <el-button size="small" native-type="submit">退出登录</el-button>
                                    </form>
                                </div>
                            </div>
                        </el-popover>
                    </div>
                </div>
                <div class="shell-hist-row">
                    <span class="hist-label">最近打开</span>
                    <div class="hist-tags">
                        <el-tag
                            v-for="t in history"
                            :key="t.path"
                            size="small"
                            effect="plain"
                            :type="t.path === activePath ? 'primary' : 'info'"
                            :closable="history.length > 1"
                            @close.stop="removeHist(t)"
                            @click="openHist(t)"
                        >@{{ t.title }}</el-tag>
                    </div>
                </div>
            </div>
            <el-main style="padding:0;flex:1;min-height:0">
                <iframe ref="contentFrame" :src="iframeSrc" style="width:100%;height:100%;border:0;display:block"></iframe>
            </el-main>
        </el-container>
    </el-container>
</div>

<script src="{{ asset('assets/admin-static/vue/vue.global.js') }}"></script>
<script src="{{ asset('assets/admin-static/axios/axios.min.js') }}"></script>
<script src="{{ asset('assets/admin-static/element-plus/index.full.min.js') }}"></script>
<script src="{{ asset('assets/admin-static/element-plus/locale/zh-cn.min.js') }}"></script>
<script src="{{ asset('assets/admin-static/element-plus-icons/index.iife.min.js') }}"></script>
<script src="{{ asset('assets/admin-static/utils/utils.js') }}"></script>
<script>
    const { createApp } = Vue;

    const ADMIN_THEMES = @json($adminThemes ?? []);
    const ADMIN_DEFAULT_THEME = @json($adminDefaultTheme ?? 'blue');
    const ADMIN_SIDEBAR_WIDTH_COLLAPSED = @json(config('admin.sidebar_width_collapsed', '64px'));
    const ADMIN_SIDEBAR_WIDTH_EXPANDED = @json(config('admin.sidebar_width_expanded', '220px'));

    const token = document.querySelector('meta[name="csrf-token"]').content;
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.withCredentials = true;
    const ADMIN_LOGIN_URL = @json(url('/admin/login'));
    axios.interceptors.response.use(
        (r) => r,
        (err) => {
            if (err.response && err.response.status === 401) {
                const topWin = window.top || window;
                topWin.location.href = ADMIN_LOGIN_URL;
            }
            return Promise.reject(err);
        }
    );

    const DEFAULT_HOME = '/admin/content/dashboard';
    const PROFILE_URL = @json(route('admin.content.profile'));

    const FALLBACK_THEME = {
        label: '科技蓝',
        primary: '#409EFF',
        sidebar_bg: '#1f2d3d',
        sidebar_logo_bg: '#1b2735',
        sidebar_text: '#bfcbd9',
        sidebar_active: '#409EFF',
        sidebar_hover_bg: 'rgba(255,255,255,0.06)',
    };

    const App = {
        data() {
            return {
                collapsed: localStorage.getItem('neo_admin_collapsed') === '1',
                themes: ADMIN_THEMES,
                themeKey: localStorage.getItem('neo_admin_theme') || ADMIN_DEFAULT_THEME,
                menuTree: [],
                activePath: DEFAULT_HOME,
                iframeSrc: DEFAULT_HOME,
                history: [],
                user: { username: '', name: '', avatar_url: '', role_name: '' },
            };
        },
        computed: {
            asideW() {
                return this.collapsed ? ADMIN_SIDEBAR_WIDTH_COLLAPSED : ADMIN_SIDEBAR_WIDTH_EXPANDED;
            },
            activeTheme() {
                const t = this.themes[this.themeKey] || this.themes[ADMIN_DEFAULT_THEME];
                return t || FALLBACK_THEME;
            },
            userInitial() {
                const s = (this.user.name || this.user.username || '?').toString();
                return s ? s.charAt(0).toUpperCase() : '?';
            },
            breadcrumbItems() {
                if (this.activePath === PROFILE_URL) {
                    return ['个人信息'];
                }
                const trail = this.findMenuTrail(this.menuTree, this.activePath);
                if (trail && trail.length) return trail;
                const title = this.findTitle(this.menuTree, this.activePath);
                if (title) return [title];
                const h = this.history.find((x) => x.path === this.activePath);
                if (h) return [h.title];
                return ['后台'];
            },
        },
        mounted() {
            this.history = this.loadHist();
            if (!this.history.some((h) => h.path === DEFAULT_HOME)) {
                this.recordHist('工作台', DEFAULT_HOME);
            }
            this.applyThemeVars();
            this.bootstrap();
            window.addEventListener('message', this.onShellMessage);
        },
        beforeUnmount() {
            window.removeEventListener('message', this.onShellMessage);
        },
        methods: {
            icon(name) {
                const lib = window.ElementPlusIconsVue || {};
                return lib[name] || lib.Menu || lib.Files;
            },
            applyThemeVars() {
                const t = this.activeTheme;
                document.documentElement.style.setProperty('--el-color-primary', t.primary);
                document.documentElement.style.setProperty(
                    '--neo-sidebar-menu-hover-bg',
                    t.sidebar_hover_bg || 'rgba(255,255,255,0.06)'
                );
                if (t.sidebar_hover_text) {
                    document.documentElement.style.setProperty('--neo-sidebar-menu-hover-color', t.sidebar_hover_text);
                } else {
                    document.documentElement.style.removeProperty('--neo-sidebar-menu-hover-color');
                }
            },
            setTheme(key) {
                if (!key || !this.themes[key]) return;
                this.themeKey = key;
                localStorage.setItem('neo_admin_theme', key);
                this.applyThemeVars();
            },
            refreshContent() {
                const iframe = this.$refs.contentFrame;
                if (iframe) {
                    try {
                        if (iframe.contentWindow) {
                            iframe.contentWindow.location.reload();
                            return;
                        }
                    } catch (e) {
                        /* ignore */
                    }
                }
                const url = this.activePath;
                this.iframeSrc = '';
                this.$nextTick(() => {
                    this.iframeSrc = url;
                });
            },
            toggleCollapse() {
                this.collapsed = !this.collapsed;
                localStorage.setItem('neo_admin_collapsed', this.collapsed ? '1' : '0');
            },
            /** 退出登录前清空「最近打开」 */
            clearRecentHistoryStorage() {
                try {
                    localStorage.removeItem('neo_admin_history_v2');
                    localStorage.removeItem('neo_admin_history');
                } catch (e) {
                    /* ignore */
                }
            },
            /** 会话是否有效 */
            async ensureSession() {
                try {
                    const { data } = await axios.get(@json(url('/admin/api/me')));

                    return data && data.code === 0;
                } catch (e) {
                    return false;
                }
            },
            loadHist() {
                try {
                    const raw = localStorage.getItem('neo_admin_history_v2');
                    if (raw) {
                        const arr = Utils.parseJson(raw, []);

                        return Array.isArray(arr) ? arr : [];
                    }
                    const legacyRaw = localStorage.getItem('neo_admin_history');
                    if (legacyRaw) {
                        const arr = Utils.parseJson(legacyRaw, []);
                        localStorage.removeItem('neo_admin_history');
                        const list = Array.isArray(arr) ? arr : [];

                        return list.slice().reverse();
                    }

                    return [];
                } catch (e) {
                    return [];
                }
            },
            saveHist() {
                localStorage.setItem('neo_admin_history_v2', JSON.stringify(this.history.slice(-10)));
            },
            /** 标签顺序固定不移动：仅在新路径时追加到右侧；已存在则原地更新标题 */
            recordHist(title, path) {
                if (!path) return;
                const i = this.history.findIndex((x) => x.path === path);
                if (i !== -1) {
                    const nt = title || this.history[i].title;
                    if (this.history[i].title !== nt) {
                        this.history.splice(i, 1, { title: nt, path });
                        this.saveHist();
                    }

                    return;
                }
                this.history.push({ title, path });
                while (this.history.length > 10) {
                    this.history.shift();
                }
                this.saveHist();
            },
            async removeHist(t) {
                if (!t || this.history.length <= 1) return;
                const wasActive = this.activePath === t.path;
                const newHist = this.history.filter((x) => x.path !== t.path);
                if (wasActive && newHist.length) {
                    if (!await this.ensureSession()) return;
                }
                this.history = newHist;
                this.saveHist();
                if (wasActive && this.history.length) {
                    const next = this.history[this.history.length - 1];
                    this.activePath = next.path;
                    this.iframeSrc = next.path;
                }
            },
            async openHist(t) {
                if (!t || !t.path) return;
                if (!await this.ensureSession()) return;
                this.activePath = t.path;
                this.iframeSrc = t.path;
            },
            findTitle(nodes, path) {
                for (const n of nodes) {
                    if (n.type === 1 && n.path === path) return n.name;
                    if (n.children && n.children.length) {
                        const hit = this.findTitle(n.children, path);
                        if (hit) return hit;
                    }
                }
                return '';
            },
            findMenuTrail(nodes, path) {
                if (!nodes || !nodes.length || !path) return null;
                for (const n of nodes) {
                    if (n.type === 1 && n.path === path) return [n.name];
                    if (n.children && n.children.length) {
                        const sub = this.findMenuTrail(n.children, path);
                        if (sub) return [n.name, ...sub];
                    }
                }
                return null;
            },
            async openProfile() {
                if (!await this.ensureSession()) return;
                this.activePath = PROFILE_URL;
                this.iframeSrc = PROFILE_URL;
                this.recordHist('个人信息', PROFILE_URL);
            },
            async onSelect(index) {
                if (!index || String(index).startsWith('dir_')) return;
                if (!await this.ensureSession()) return;
                const title = this.findTitle(this.menuTree, index) || '页面';
                this.activePath = index;
                this.iframeSrc = index;
                this.recordHist(title, index);
            },
            onShellMessage(ev) {
                const d = ev && ev.data;
                if (d && d.type === 'neo-admin-profile-updated') {
                    this.refreshUserBar();
                }
            },
            async refreshUserBar() {
                try {
                    const { data } = await axios.get(@json(url('/admin/api/me')));
                    if (data.code !== 0) return;
                    const u = data.data || {};
                    this.user = {
                        username: u.username,
                        name: u.name || '',
                        avatar_url: u.avatar_url || '',
                        role_name: u.role_name || '',
                    };
                } catch (e) {
                    /* ignore */
                }
            },
            async bootstrap() {
                const [mRes, uRes] = await Promise.all([
                    axios.get(@json(url('/admin/api/sidebar/menus'))),
                    axios.get(@json(url('/admin/api/me'))),
                ]);
                if (mRes.data.code === 0) this.menuTree = mRes.data.data || [];
                if (uRes.data.code === 0) {
                    const u = uRes.data.data || {};
                    this.user = {
                        username: u.username,
                        name: u.name || '',
                        avatar_url: u.avatar_url || '',
                        role_name: u.role_name || '',
                    };
                }
            },
        },
    };

    const zhCn = window.ElementPlusLocaleZhCn || {};
    const app = createApp(App);
    app.use(ElementPlus, { locale: zhCn });
    const icons = window.ElementPlusIconsVue || {};
    for (const [key, comp] of Object.entries(icons)) {
        app.component(key, comp);
    }
    app.mount('#shell');
</script>
</body>
</html>
