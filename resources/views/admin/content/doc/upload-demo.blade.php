@extends('admin.layouts.content')

@section('title', '文件上传示例')

@push('scripts')
    <script>window.__DOC_UPLOAD_PAGE__ = @json($uploadPage);</script>
    <script src="{{ asset('js/neo-upload.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp } = Vue;
            const zhCn = window.ElementPlusLocaleZhCn || {};
            neoAxiosSetup(axios);

            createApp({
                data() {
                    return {
                        avatarPath: '',
                        docPath: '',
                    };
                },
                computed: {
                    page() {
                        return window.__DOC_UPLOAD_PAGE__ || {};
                    },
                },
                template: `
                    <div style="max-width:720px;">
                        <el-card shadow="never" style="border:1px solid #ebeef5;margin-bottom:16px;">
                            <template #header><span style="font-weight:600;">说明</span></template>
                            <div style="color:#606266;font-size:14px;line-height:1.75;">
                                <p style="margin:0 0 8px;">上传接口：<code style="background:#f4f4f5;padding:2px 6px;border-radius:4px;">POST /admin/api/upload</code>，表单字段 <code>file</code> + <code>scene</code>。</p>
                                <p style="margin:0 0 8px;">限制与目录见 <strong>config/upload.php</strong>；头像 <code>UPLOAD_AVATAR_MAX_KB</code>，文档 <code>UPLOAD_DOCUMENT_MAX_KB</code>。</p>
                                <p style="margin:0;">公开访问需 <code>php artisan storage:link</code>。组件 <strong>NeoUploadField</strong>，脚本 <code>neo-upload.js</code>。</p>
                            </div>
                        </el-card>
                        <el-card shadow="never" style="border:1px solid #ebeef5;margin-bottom:16px;">
                            <template #header><span style="font-weight:600;">场景 avatar · 图片</span></template>
                            <NeoUploadField
                                v-if="page.uploadUrl"
                                v-model="avatarPath"
                                :action="page.uploadUrl"
                                scene="avatar"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                tip="弹窗内可从资源库选用或上传；path 可写入用户 avatar 字段"
                                :storage-base="page.storagePublicBase || ''"
                                :resource-list-url="page.resourcesListUrl || '/admin/api/resources'"
                            />
                            <el-input v-model="avatarPath" clearable style="margin-top:10px" placeholder="上传后的 path 或外链" />
                        </el-card>
                        <el-card shadow="never" style="border:1px solid #ebeef5;">
                            <template #header><span style="font-weight:600;">场景 document · 附件</span></template>
                            <NeoUploadField
                                v-if="page.uploadUrl"
                                v-model="docPath"
                                :action="page.uploadUrl"
                                scene="document"
                                accept=".pdf,.doc,.docx,.xlsx,.zip,.png,.jpg,.jpeg,.txt"
                                tip="弹窗内可从资源库选用或上传；规则见 config/upload.php → document"
                                :storage-base="page.storagePublicBase || ''"
                                :resource-list-url="page.resourcesListUrl || '/admin/api/resources'"
                            />
                            <el-input v-model="docPath" clearable style="margin-top:10px" placeholder="上传后的 path" />
                        </el-card>
                    </div>
                `,
            }).use(ElementPlus, { locale: zhCn }).component('NeoUploadField', window.NeoUploadField).mount('#app');
        });
    </script>
@endpush
