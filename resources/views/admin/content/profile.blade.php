@extends('admin.layouts.content')

@section('title', '个人信息')

@push('scripts')
    <script>
        window.__PROFILE_PAGE__ = @json($profilePage);
    </script>
    <script src="{{ asset('js/neo-upload.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp } = Vue;
            const zhCn = window.ElementPlusLocaleZhCn || {};
            neoAxiosSetup(axios);

            const AppRoot = {
                data() {
                    return {
                        loading: true,
                        saving: false,
                        roleName: '',
                        form: { username: '', name: '', email: '', avatar: '' },
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
                            this.form = {
                                username: u.username || '',
                                name: u.name || '',
                                email: u.email || '',
                                avatar: u.avatar != null ? String(u.avatar) : '',
                            };
                        } catch (e) {
                            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '加载失败');
                        } finally {
                            this.loading = false;
                        }
                    },
                    async save() {
                        this.saving = true;
                        try {
                            const { data } = await axios.put(this.page.meUrl, {
                                name: this.form.name,
                                email: this.form.email || null,
                                avatar: this.form.avatar || null,
                            });
                            if (data.code !== 0) throw new Error(data.message || '保存失败');
                            ElementPlus.ElMessage.success('保存成功');
                            if (window.parent && window.parent !== window) {
                                window.parent.postMessage({ type: 'neo-admin-profile-updated' }, '*');
                            }
                        } catch (e) {
                            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '保存失败');
                        } finally {
                            this.saving = false;
                        }
                    },
                },
                template: `
                    <div v-if="loading" style="color:#909399;font-size:14px;">加载中…</div>
                    <el-card v-else shadow="never" style="max-width:560px;border:1px solid #ebeef5;">
                        <template #header><span style="font-weight:600;">个人资料</span></template>
                        <el-form label-width="88px" style="max-width:480px;">
                            <el-form-item label="账号">
                                <el-input v-model="form.username" disabled />
                            </el-form-item>
                            <el-form-item label="角色">
                                <span style="color:#606266;">@{{ roleName }}</span>
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
                            <el-form-item>
                                <el-button type="primary" :loading="saving" @click="save">保存修改</el-button>
                            </el-form-item>
                        </el-form>
                    </el-card>
                `,
                computed: {
                    page() {
                        return window.__PROFILE_PAGE__ || {};
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
