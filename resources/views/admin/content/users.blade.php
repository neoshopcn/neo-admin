@extends('admin.layouts.content')

@section('title', '用户管理')

@push('scripts')
    <style>
        .google2fa-setup-box { --google2fa-setup-width: 400px; max-width: var(--google2fa-setup-width); width: var(--google2fa-setup-width); border-radius: 12px; overflow: hidden; }
        .google2fa-setup-box .el-message-box__header { padding: 18px 20px 6px; }
        .google2fa-setup-box .el-message-box__title { font-size: 16px; font-weight: 600; color: #303133; }
        .google2fa-setup-box .el-message-box__content { padding: 6px 20px 4px; }
        .google2fa-setup-box .el-message-box__btns {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            padding: 12px 20px 18px;
        }
        .google2fa-setup-box .el-message-box__btns .el-button { min-width: 108px; margin: 0 !important; }
        .google2fa-setup__tip {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            background: linear-gradient(135deg, #ecf5ff 0%, #f4faff 100%);
            border: 1px solid #d9ecff;
            border-radius: 10px;
            color: #606266;
            font-size: 13px;
            line-height: 1.65;
        }
        .google2fa-setup__tip-icon {
            flex-shrink: 0;
            width: 22px;
            height: 22px;
            margin-top: 1px;
            color: var(--el-color-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .google2fa-setup__tip-icon svg { width: 20px; height: 20px; }
        .google2fa-setup__qr-wrap { text-align: center; margin-bottom: 14px; }
        .google2fa-setup__qr {
            display: inline-block;
            width: 196px;
            height: 196px;
            padding: 10px;
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(31, 45, 61, 0.08);
        }
        .google2fa-setup__secret-wrap { text-align: center; padding-bottom: 4px; }
        .google2fa-setup__secret-label {
            display: block;
            font-size: 12px;
            color: #909399;
            margin-bottom: 8px;
        }
        .google2fa-setup__secret {
            display: inline-block;
            padding: 10px 18px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 15px;
            letter-spacing: 0.14em;
            color: #303133;
            background: #f5f7fa;
            border: 1px dashed #dcdfe6;
            border-radius: 8px;
            user-select: all;
            word-break: break-all;
        }
    </style>
    <script>window.__NEO_CONFIG__ = @json($neo);</script>
    <script src="{{ asset('js/neo-upload.js') }}"></script>
    <script src="{{ asset('js/neo-table.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            neoAxiosSetup(axios);
            const baseUrl = window.__NEO_CONFIG__.listUrl.replace(/\/$/, '');

            function copyGoogle2faSecret(secret) {
                const done = function () {
                    ElementPlus.ElMessage.success('密钥已复制到剪贴板');
                };
                const fail = function () {
                    ElementPlus.ElMessage.error('复制失败，请手动选择密钥');
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(secret).then(done).catch(function () {
                        const ta = document.createElement('textarea');
                        ta.value = secret;
                        ta.style.position = 'fixed';
                        ta.style.left = '-9999px';
                        document.body.appendChild(ta);
                        ta.select();
                        try {
                            document.execCommand('copy') ? done() : fail();
                        } catch (e) {
                            fail();
                        }
                        document.body.removeChild(ta);
                    });
                    return;
                }
                fail();
            }

            function showGoogle2faSetupDialog(secret, qrUrl) {
                const icons = window.ElementPlusIconsVue || {};
                const { h } = Vue;
                const TipIcon = icons.Cellphone || icons.Iphone || icons.Key;

                const message = h('div', { class: 'google2fa-setup' }, [
                    h('div', { class: 'google2fa-setup__tip' }, [
                        TipIcon ? h('span', { class: 'google2fa-setup__tip-icon' }, [h(TipIcon)]) : null,
                        h('span', { class: 'google2fa-setup__tip-text' }, '请使用 Google Authenticator 等应用扫描下方二维码，或手动输入密钥。'),
                    ]),
                    qrUrl ? h('div', { class: 'google2fa-setup__qr-wrap' }, [
                        h('img', { src: qrUrl, alt: '双因子二维码', class: 'google2fa-setup__qr' }),
                    ]) : null,
                    h('div', { class: 'google2fa-setup__secret-wrap' }, [
                        // h('span', { class: 'google2fa-setup__secret-label' }, '密钥'),
                        h('code', { class: 'google2fa-setup__secret' }, secret),
                    ]),
                ]);

                return ElementPlus.ElMessageBox({
                    title: '双因子已开启',
                    message: message,
                    customClass: 'google2fa-setup-box',
                    showCancelButton: true,
                    showConfirmButton: true,
                    cancelButtonText: '复制密钥',
                    confirmButtonText: '确认关闭',
                    distinguishCancelAndClose: true,
                    closeOnClickModal: false,
                    beforeClose: function (action, instance, done) {
                        if (action === 'cancel') {
                            copyGoogle2faSecret(secret);
                            return;
                        }
                        done();
                    },
                });
            }

            mountNeoTable('#app', window.__NEO_CONFIG__, {
                onExtra: async function (action, row, vm) {
                    const id = row[window.__NEO_CONFIG__.rowKey || 'id'];

                    if (action === 'resetPwd') {
                        try {
                            await ElementPlus.ElMessageBox.confirm('确定为该用户重置随机密码？', '提示', { type: 'warning' });
                        } catch (e) {
                            return;
                        }
                        try {
                            const { data } = await axios.post(`${baseUrl}/${id}/reset-password`, {});
                            if (data.code !== 0) throw new Error(data.message || '失败');
                            const pwd = data.data?.password_plain || '';
                            await ElementPlus.ElMessageBox.alert(`新密码：${pwd}`, '重置成功', { type: 'success' });
                        } catch (e) {
                            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '重置失败');
                        }
                        return;
                    }

                    if (action === 'enable2fa') {
                        try {
                            await ElementPlus.ElMessageBox.confirm(
                                '确定为该用户开启谷歌双因子验证？开启后登录需输入验证器动态码。',
                                '开启双因子',
                                { type: 'warning' }
                            );
                        } catch (e) {
                            return;
                        }
                        try {
                            const { data } = await axios.post(`${baseUrl}/${id}/enable-google2fa`, {});
                            if (data.code !== 0) throw new Error(data.message || '开启失败');
                            const secret = data.data?.secret || '';
                            const qrUrl = data.data?.qr_url || '';
                            try {
                                await showGoogle2faSetupDialog(secret, qrUrl);
                            } catch (e) {
                                if (e !== 'cancel' && e !== 'close') throw e;
                            } finally {
                                vm.loadList();
                            }
                        } catch (e) {
                            if (e !== 'cancel' && e !== 'close') {
                                ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '开启失败');
                            }
                        }
                        return;
                    }

                    if (action === 'disable2fa') {
                        try {
                            await ElementPlus.ElMessageBox.confirm(
                                '关闭后该用户登录将不再需要双因子验证码，密钥将被清除。',
                                '关闭双因子',
                                { type: 'warning' }
                            );
                        } catch (e) {
                            return;
                        }
                        try {
                            const { data } = await axios.post(`${baseUrl}/${id}/disable-google2fa`, {});
                            if (data.code !== 0) throw new Error(data.message || '关闭失败');
                            ElementPlus.ElMessage.success('已关闭双因子验证');
                            vm.loadList();
                        } catch (e) {
                            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '关闭失败');
                        }
                        return;
                    }

                    if (action === 'unlock2fa') {
                        try {
                            await ElementPlus.ElMessageBox.confirm('确定解除该用户的双因子验证锁定？', '解除锁定', { type: 'warning' });
                        } catch (e) {
                            return;
                        }
                        try {
                            const { data } = await axios.post(`${baseUrl}/${id}/unlock-google2fa`, {});
                            if (data.code !== 0) throw new Error(data.message || '解除失败');
                            ElementPlus.ElMessage.success('已解除锁定');
                            vm.loadList();
                        } catch (e) {
                            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '解除失败');
                        }
                    }
                },
            });
        });
    </script>
@endpush
