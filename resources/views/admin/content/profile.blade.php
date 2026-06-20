@extends('admin.layouts.content')

@section('title', '个人信息')

@push('scripts')
    <script>
        window.__PROFILE_PAGE__ = @json($profilePage);
    </script>
    <style>
        .neo-wrap.neo-profile-root {
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .neo-profile-page {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #f5f7fa;
        }
        .neo-profile-loading {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #909399;
            font-size: 14px;
            gap: 10px;
        }
        .neo-profile-hero {
            position: relative;
            padding: 28px 28px 72px;
            background: linear-gradient(135deg, #ecf5ff 0%, #f5f7fa 45%, #faf6ff 100%);
            border-bottom: 1px solid #ebeef5;
            overflow: hidden;
        }
        .neo-profile-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 12% 20%, rgba(64, 158, 255, 0.14) 0%, transparent 42%),
                radial-gradient(circle at 88% 10%, rgba(168, 126, 252, 0.12) 0%, transparent 38%);
            pointer-events: none;
        }
        .neo-profile-hero-inner {
            position: relative;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }
        .neo-profile-avatar {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, var(--el-color-primary) 0%, #66b1ff 50%, #a77efc 100%);
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            box-shadow: 0 8px 24px rgba(64, 158, 255, 0.28);
            border: 3px solid rgba(255, 255, 255, 0.92);
        }
        .neo-profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .neo-profile-hero-text { min-width: 0; }
        .neo-profile-hero-kicker {
            font-size: 12px;
            color: #909399;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }
        .neo-profile-hero-name {
            margin: 0 0 8px;
            font-size: 24px;
            font-weight: 700;
            color: #303133;
            line-height: 1.3;
        }
        .neo-profile-hero-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #606266;
        }
        .neo-profile-body {
            flex: 1;
            margin-top: -44px;
            padding: 0 20px 28px;
            position: relative;
            z-index: 1;
        }
        .neo-profile-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            align-items: start;
        }
        @media (max-width: 900px) {
            .neo-profile-grid { grid-template-columns: 1fr; }
            .neo-profile-hero { padding: 22px 16px 64px; }
            .neo-profile-body { padding: 0 12px 24px; }
        }
        .neo-profile-card {
            border: 1px solid #ebeef5;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .neo-profile-card .el-card__header {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f2f5;
            font-weight: 600;
            font-size: 15px;
            color: #303133;
        }
        .neo-profile-card .el-card__body {
            padding: 20px 20px 8px;
        }
        .neo-profile-card-desc {
            margin: -4px 0 16px;
            font-size: 13px;
            color: #909399;
            line-height: 1.5;
        }
        .neo-profile-form .el-form-item:last-child { margin-bottom: 12px; }
        .neo-profile-card-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 4px;
            margin-top: 4px;
            border-top: 1px solid #f0f2f5;
        }
    </style>
    <script src="{{ asset('js/neo-upload.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('app').classList.add('neo-profile-root');

            const { createApp } = Vue;
            const zhCn = window.ElementPlusLocaleZhCn || {};
            neoAxiosSetup(axios);

            const AppRoot = {
                data() {
                    return {
                        loading: true,
                        savingBasic: false,
                        savingPassword: false,
                        roleName: '',
                        avatarUrl: '',
                        form: {
                            username: '',
                            name: '',
                            email: '',
                            avatar: '',
                            current_password: '',
                            password: '',
                            password_confirmation: '',
                        },
                    };
                },
                mounted() {
                    this.loadMe();
                },
                methods: {
                    async loadMe() {
                        this.loading = true;
                        try {
                            const { data } = await axios.get(this.page.meUrl);
                            if (data.code !== 0) throw new Error(data.message || '加载失败');
                            const u = data.data || {};
                            this.roleName = u.role_name || '未分配角色';
                            this.avatarUrl = u.avatar_url || '';
                            this.form = {
                                username: u.username || '',
                                name: u.name || '',
                                email: u.email || '',
                                avatar: u.avatar != null ? String(u.avatar) : '',
                                current_password: '',
                                password: '',
                                password_confirmation: '',
                            };
                        } catch (e) {
                            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '加载失败');
                        } finally {
                            this.loading = false;
                        }
                    },
                    notifyParent() {
                        if (window.parent && window.parent !== window) {
                            window.parent.postMessage({ type: 'neo-admin-profile-updated' }, '*');
                        }
                    },
                    applyUserResponse(u) {
                        if (!u || typeof u !== 'object') return;
                        this.avatarUrl = u.avatar_url || '';
                        if (u.name != null) this.form.name = u.name;
                        if (u.email != null) this.form.email = u.email;
                        if (u.avatar != null) this.form.avatar = String(u.avatar);
                    },
                    async saveBasic() {
                        this.savingBasic = true;
                        try {
                            const { data } = await axios.put(this.page.meUrl, {
                                scope: 'basic',
                                name: this.form.name,
                                email: this.form.email || null,
                                avatar: this.form.avatar || null,
                            });
                            if (data.code !== 0) throw new Error(data.message || '保存失败');
                            this.applyUserResponse(data.data);
                            ElementPlus.ElMessage.success('基本信息已保存');
                            this.notifyParent();
                        } catch (e) {
                            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '保存失败');
                        } finally {
                            this.savingBasic = false;
                        }
                    },
                    async savePassword() {
                        if (!this.form.current_password) {
                            ElementPlus.ElMessage.warning('请填写当前密码');
                            return;
                        }
                        if (!this.form.password) {
                            ElementPlus.ElMessage.warning('请填写新密码');
                            return;
                        }
                        if (this.form.password !== this.form.password_confirmation) {
                            ElementPlus.ElMessage.warning('两次输入的新密码不一致');
                            return;
                        }
                        this.savingPassword = true;
                        try {
                            const { data } = await axios.put(this.page.meUrl, {
                                scope: 'password',
                                current_password: this.form.current_password,
                                password: this.form.password,
                                password_confirmation: this.form.password_confirmation,
                            });
                            if (data.code !== 0) throw new Error(data.message || '保存失败');
                            ElementPlus.ElMessage.success('密码已修改');
                            this.form.current_password = '';
                            this.form.password = '';
                            this.form.password_confirmation = '';
                        } catch (e) {
                            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '保存失败');
                        } finally {
                            this.savingPassword = false;
                        }
                    },
                },
                template: `
                    <div class="neo-profile-page">
                        <div v-if="loading" class="neo-profile-loading">
                            <span>加载中…</span>
                        </div>
                        <template v-else>
                            <section class="neo-profile-hero">
                                <div class="neo-profile-hero-inner">
                                    <div class="neo-profile-avatar">
                                        <img v-if="avatarUrl" :src="avatarUrl" alt="头像" />
                                        <span v-else>@{{ avatarInitial }}</span>
                                    </div>
                                    <div class="neo-profile-hero-text">
                                        <div class="neo-profile-hero-kicker">个人中心</div>
                                        <h1 class="neo-profile-hero-name">@{{ form.name || form.username || '未设置姓名' }}</h1>
                                        <div class="neo-profile-hero-meta">
                                            <span>@{{ form.username }}</span>
                                            <el-tag size="small" type="info" effect="plain">@{{ roleName }}</el-tag>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <div class="neo-profile-body">
                                <div class="neo-profile-grid">
                                    <el-card shadow="never" class="neo-profile-card">
                                        <template #header>基本信息</template>
                                        <p class="neo-profile-card-desc">更新您的姓名、邮箱与头像，保存后立即生效。</p>
                                        <el-form label-width="88px" class="neo-profile-form">
                                            <el-form-item label="账号">
                                                <el-input v-model="form.username" disabled />
                                            </el-form-item>
                                            <el-form-item label="角色">
                                                <el-input v-model="roleName" disabled />
                                            </el-form-item>
                                            <el-form-item label="姓名" required>
                                                <el-input v-model="form.name" maxlength="120" show-word-limit placeholder="姓名" />
                                            </el-form-item>
                                            <el-form-item label="邮箱">
                                                <el-input v-model="form.email" type="email" maxlength="190" placeholder="选填" clearable />
                                            </el-form-item>
                                            <el-form-item label="头像">
                                                <NeoUploadField
                                                    v-if="page.uploadUrl"
                                                    :model-value="form.avatar"
                                                    @update:model-value="(v) => { form.avatar = v; }"
                                                    :action="page.uploadUrl"
                                                    scene="avatar"
                                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                                    tip="从资源库选择或上传"
                                                    :storage-base="page.storagePublicBase || ''"
                                                    :resource-list-url="page.resourcesListUrl || '/admin/api/resources'"
                                                />
                                                <el-input v-model="form.avatar" clearable style="margin-top:10px"
                                                    placeholder="存储路径或图片 URL" />
                                            </el-form-item>
                                            <div class="neo-profile-card-actions">
                                                <el-button type="primary" :loading="savingBasic" @click="saveBasic">保存基本信息</el-button>
                                            </div>
                                        </el-form>
                                    </el-card>

                                    <el-card shadow="never" class="neo-profile-card">
                                        <template #header>安全设置</template>
                                        <p class="neo-profile-card-desc">修改密码需验证当前密码，保存后请使用新密码登录。</p>
                                        <el-form label-width="96px" class="neo-profile-form">
                                            <el-form-item label="当前密码">
                                                <el-input v-model="form.current_password" type="password" show-password
                                                    autocomplete="current-password" placeholder="修改密码时必填" />
                                            </el-form-item>
                                            <el-form-item label="新密码">
                                                <el-input v-model="form.password" type="password" show-password
                                                    autocomplete="new-password" placeholder="至少 6 位" />
                                            </el-form-item>
                                            <el-form-item label="确认新密码">
                                                <el-input v-model="form.password_confirmation" type="password" show-password
                                                    autocomplete="new-password" placeholder="再次输入新密码" />
                                            </el-form-item>
                                            <div class="neo-profile-card-actions">
                                                <el-button type="primary" :loading="savingPassword" @click="savePassword">保存密码</el-button>
                                            </div>
                                        </el-form>
                                    </el-card>
                                </div>
                            </div>
                        </template>
                    </div>
                `,
                computed: {
                    page() {
                        return window.__PROFILE_PAGE__ || {};
                    },
                    avatarInitial() {
                        const s = (this.form.name || this.form.username || '?').trim();
                        return s ? s.charAt(0).toUpperCase() : '?';
                    },
                },
            };

            const app = createApp(AppRoot);
            app.use(ElementPlus, { locale: zhCn });
            if (window.NeoUploadField) {
                app.component('NeoUploadField', window.NeoUploadField);
            }
            app.mount('#app');
        });
    </script>
@endpush
