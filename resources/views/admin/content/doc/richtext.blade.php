@extends('admin.layouts.content')

@section('title', '富文本示例')

@push('scripts')
    <link href="{{ asset('assets/admin-static/quill/quill.snow.css') }}" rel="stylesheet"/>
    <script src="{{ asset('assets/admin-static/quill/quill.min.js') }}"></script>
    <script>window.__DOC_RICHTEXT__ = @json($richtextPage);</script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp } = Vue;
            const zhCn = window.ElementPlusLocaleZhCn || {};
            neoAxiosSetup(axios);

            const toolbarOptions = [
                [{ header: [1, 2, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'image'],
                ['clean'],
            ];

            createApp({
                data() {
                    return { quill: null };
                },
                mounted() {
                    const self = this;
                    this.$nextTick(() => {
                        if (typeof Quill === 'undefined') {
                            ElementPlus.ElMessage.error('富文本脚本加载失败');
                            return;
                        }
                        this.quill = new Quill('#quill-editor', {
                            theme: 'snow',
                            placeholder: '在此编辑… 工具栏图片可本地上传',
                            modules: {
                                toolbar: {
                                    container: toolbarOptions,
                                    handlers: {
                                        image: function () {
                                            self.pickAndUploadImage();
                                        },
                                    },
                                },
                            },
                        });
                        this.quill.root.innerHTML = '<p>欢迎使用 <strong>Quill</strong>。工具栏 <strong>图片</strong> 本地上传：<code>POST /admin/api/upload</code>，场景 <code>richtext</code>，配置见 <code>config/upload.php</code>。</p><p><code>v-html</code> 渲染列表前须做 XSS 过滤。</p>';
                    });
                },
                methods: {
                    uploadPage() {
                        return window.__DOC_RICHTEXT__ || {};
                    },
                    pickAndUploadImage() {
                        const q = this.quill;
                        const uploadUrl = this.uploadPage().uploadUrl;
                        if (!q) return;
                        if (!uploadUrl) {
                            ElementPlus.ElMessage.warning('未配置上传地址');
                            return;
                        }
                        const input = document.createElement('input');
                        input.type = 'file';
                        input.accept = 'image/jpeg,image/png,image/gif,image/webp';
                        input.onchange = async () => {
                            const file = input.files && input.files[0];
                            if (!file) return;
                            const fd = new FormData();
                            fd.append('file', file);
                            fd.append('scene', 'richtext');
                            let loading = null;
                            try {
                                loading = ElementPlus.ElLoading.service({
                                    lock: true,
                                    text: '上传中…',
                                    background: 'rgba(255,255,255,0.65)',
                                });
                                const { data } = await axios.post(uploadUrl, fd);
                                if (data.code !== 0) throw new Error(data.message || '上传失败');
                                const imgUrl = data.data && data.data.url ? data.data.url : '';
                                if (!imgUrl) throw new Error('未返回图片地址');
                                const range = q.getSelection(true);
                                const idx = range ? range.index : q.getLength();
                                q.insertEmbed(idx, 'image', imgUrl);
                                q.setSelection(idx + 1, 0);
                                ElementPlus.ElMessage.success('图片已插入');
                            } catch (e) {
                                ElementPlus.ElMessage.error(e.response && e.response.data && e.response.data.message ? e.response.data.message : e.message || '上传失败');
                            } finally {
                                if (loading) loading.close();
                            }
                        };
                        input.click();
                    },
                    showHtml() {
                        if (!this.quill) return;
                        const html = this.quill.root.innerHTML;
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
                                <p style="margin:0 0 8px;">内嵌页加载 <strong>Quill</strong> Snow。工具栏 <strong>图片</strong> 走上传接口，场景 <code>richtext</code>。</p>
                                <p style="margin:0;">接入步骤简述：引入 CSS/JS → 配置 toolbar.handlers.image → 选择文件后 FormData POST 上传接口 → insertEmbed 插入图片 URL；保存时读取 root.innerHTML 或 Delta。</p>
                            </div>
                        </el-card>
                        <el-card shadow="never" style="border:1px solid #ebeef5;">
                            <template #header>
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                                    <span style="font-weight:600;">编辑器</span>
                                    <el-button size="small" type="primary" plain @@click="showHtml">查看 HTML</el-button>
                                </div>
                            </template>
                            <div id="quill-editor" style="min-height:260px;"></div>
                        </el-card>
                    </div>
                `,
            }).use(ElementPlus, { locale: zhCn }).mount('#app');
        });
    </script>
@endpush
