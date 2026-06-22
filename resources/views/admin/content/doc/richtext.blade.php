@extends('admin.layouts.content')

@section('title', '富文本示例')

@push('scripts')
    <link rel="stylesheet" href="{{ asset('assets/admin-static/jodit/jodit.min.css') }}"/>
    <script src="{{ asset('assets/admin-static/jodit/jodit.min.js') }}"></script>
    <script src="{{ asset('js/neo-upload.js') }}"></script>
    <script src="{{ asset('js/neo-richtext.js') }}"></script>
    <script>window.__DOC_RICHTEXT__ = @json($richtextPage);</script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp } = Vue;
            const zhCn = window.ElementPlusLocaleZhCn || {};
            neoAxiosSetup(axios);

            const joditBase = @json(asset('assets/admin-static/jodit'));
            const demoHtml = '<p>欢迎使用 <strong>neo-richtext</strong>（<strong>Jodit</strong>，MIT）。工具栏 <strong>图片</strong> 从资源库选择，场景 <code>richtext</code>，配置见 <code>config/upload.php</code>。</p><p>支持 <code>v-model</code> 双向绑定 HTML、图片拖拽缩放、源码模式；<code>v-html</code> 渲染前须做 XSS 过滤。</p>';

            createApp({
                data() {
                    return {
                        content: demoHtml,
                        joditBase: joditBase,
                    };
                },
                computed: {
                    page() {
                        return window.__DOC_RICHTEXT__ || {};
                    },
                },
                methods: {
                    showHtml() {
                        const html = this.$refs.editor ? this.$refs.editor.getContent() : this.content;
                        ElementPlus.ElMessageBox.alert(
                            '<pre style="white-space:pre-wrap;font-size:12px;max-height:320px;overflow:auto;">' +
                            html.replace(/</g, '&lt;').replace(/>/g, '&gt;') +
                            '</pre>',
                            '当前 HTML',
                            { dangerouslyUseHTMLString: true, confirmButtonText: '关闭' }
                        );
                    },
                },
                template: `
                    <div style="max-width:900px;">
                        <el-card shadow="never" style="border:1px solid #ebeef5;margin-bottom:16px;">
                            <template #header><span style="font-weight:600;">说明</span></template>
                            <div style="color:#606266;font-size:14px;line-height:1.75;">
                                <p style="margin:0 0 8px;">组件 <strong>neo-richtext</strong>（<code>neo-richtext.js</code>），基于 <strong>Jodit</strong>（MIT），依赖 <code>jodit.min.js</code>、<code>neo-upload.js</code>。</p>
                                <p style="margin:0 0 8px;">引入 CSS/JS 后注册组件，使用 <code>v-model</code> 绑定 HTML；图片走资源库，场景默认 <code>richtext</code>。</p>
                                <p style="margin:0;">保存/提交时读取绑定的 <code>content</code> 或 <code>ref.getContent()</code> 即可。</p>
                            </div>
                        </el-card>
                        <el-card shadow="never" style="border:1px solid #ebeef5;">
                            <template #header>
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                                    <span style="font-weight:600;">编辑器</span>
                                    <el-button size="small" type="primary" plain @@click="showHtml">查看 HTML</el-button>
                                </div>
                            </template>
                            <neo-richtext
                                v-if="page.uploadUrl"
                                ref="editor"
                                v-model="content"
                                :upload-url="page.uploadUrl"
                                :storage-public-base="page.storagePublicBase || ''"
                                :resource-list-url="page.resourcesListUrl || '/admin/api/resources'"
                                :jodit-base="joditBase"
                            />
                        </el-card>
                    </div>
                `,
            })
                .use(ElementPlus, { locale: zhCn })
                .component('neo-richtext', window.NeoRichtext)
                .mount('#app');
        });
    </script>
@endpush
