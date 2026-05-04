@extends('admin.layouts.content')

@section('title', '系统信息')

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp } = Vue;
            const zhCn = window.ElementPlusLocaleZhCn || {};

            const serverRows = @json(collect($server)->map(fn ($v, $k) => ['label' => $k, 'value' => $v])->values());
            const projectRows = @json(collect($project)->map(fn ($v, $k) => ['label' => $k, 'value' => $v])->values());

            createApp({
                data() {
                    return { serverRows, projectRows };
                },
                template: `
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px;align-items:start;">
                        <el-card shadow="never" style="border:1px solid #ebeef5;">
                            <template #header><span style="font-weight:600;">服务器信息</span></template>
                            <el-descriptions :column="1" border size="small">
                                <el-descriptions-item v-for="(row, i) in serverRows" :key="'s'+i" :label="row.label">
                                    @{{ row.value }}
                                </el-descriptions-item>
                            </el-descriptions>
                        </el-card>
                        <el-card shadow="never" style="border:1px solid #ebeef5;">
                            <template #header><span style="font-weight:600;">项目信息</span></template>
                            <el-descriptions :column="1" border size="small">
                                <el-descriptions-item v-for="(row, i) in projectRows" :key="'p'+i" :label="row.label">
                                    @{{ row.value }}
                                </el-descriptions-item>
                            </el-descriptions>
                        </el-card>
                    </div>
                `,
            }).use(ElementPlus, { locale: zhCn }).mount('#app');
        });
    </script>
@endpush
