@extends('admin.layouts.content')

@section('title', '操作日志')

@push('scripts')
    <script>window.__NEO_CONFIG__ = @json($neo);</script>
    <script src="{{ asset('js/neo-table.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            neoAxiosSetup(axios);
            const cfg = window.__NEO_CONFIG__;
            mountNeoTable('#app', cfg, {
                onBatchAction: async function (key, rows, vm) {
                    if (key !== 'delete' || !rows.length) return;
                    try {
                        await ElementPlus.ElMessageBox.confirm(
                            '确定删除选中的 ' + rows.length + ' 条操作日志？删除后不可恢复。',
                            '提示',
                            { type: 'warning' }
                        );
                    } catch (e) {
                        return;
                    }
                    try {
                        const { data } = await axios.post(cfg.batchDeleteUrl, {
                            ids: rows.map((r) => r.id),
                        });
                        if (data.code !== 0) throw new Error(data.message || '删除失败');
                        ElementPlus.ElMessage.success('已删除 ' + (data.data?.deleted ?? rows.length) + ' 条');
                        vm.loadList();
                    } catch (e) {
                        ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '删除失败');
                    }
                },
            });
        });
    </script>
@endpush
