@extends('admin.layouts.content')

@section('title', '表格操作示例')

@push('scripts')
    <script>window.__DOC_TABLE_CFG__ = @json($tableCfg);</script>
    <script src="{{ asset('js/neo-table.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            neoAxiosSetup(axios);
            var cfg = window.__DOC_TABLE_CFG__ || {};
            mountNeoTable('#app', cfg, {
                onBatchAction: function (key, rows, ctx) {
                    if (key === 'demoBatch') {
                        ElementPlus.ElMessage.success('批量演示：已选 ' + rows.length + ' 条。可在 hooks.onBatchAction 调用后端接口。');
                    }
                },
                onInlineNumberChange: function (prop, row, val, ctx) {
                    if (prop === 'sort_order') {
                        ElementPlus.ElMessage.success('排序已改为 ' + val);
                    }
                },
                onInlineSelectChange: async function (prop, row, val, ctx) {
                    if (prop !== 'status') return;
                    var url = ctx.cfg.demoPatchUrl;
                    if (!url) {
                        row.status = val;
                        ElementPlus.ElMessage.success(Number(val) === 1 ? '已设为启用' : '已设为禁用');
                        return;
                    }
                    try {
                        await axios.post(url, { id: row.id, status: val });
                        ElementPlus.ElMessage.success(Number(val) === 1 ? '已设为启用' : '已设为禁用');
                        await ctx.loadList();
                    } catch (e) {
                        ElementPlus.ElMessage.error(e.response && e.response.data && e.response.data.message ? e.response.data.message : e.message || '保存失败');
                    }
                },
            });
        });
    </script>
@endpush
