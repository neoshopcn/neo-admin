@extends('admin.layouts.content')

@section('title', '回收站管理')

@push('scripts')
    <script>
        window.__RECYCLE_BIN_PAGE__ = @json($recycleBinPage);
    </script>
    <style>
        .neo-rb-hd { margin-bottom: 12px; font-size: 15px; font-weight: 600; color: #303133; }
        .neo-rb-json-wrap { height: calc(100vh - 140px); min-height: 520px; box-sizing: border-box; }
        .neo-rb-json {
            font-family: ui-monospace, monospace;
            font-size: 12px;
            color: #606266;
            white-space: pre-wrap;
            word-break: break-all;
            margin: 0;
            height: 100%;
            max-height: none;
            overflow: auto;
            padding: 8px 4px;
            box-sizing: border-box;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp } = Vue;
            const zhCn = window.ElementPlusLocaleZhCn || {};
            const cfg = window.__RECYCLE_BIN_PAGE__ || {};
            const permCodes = window.__ADMIN_PERM_CODES__ || [];

            createApp({
                data() {
                    return {
                        keyword: '',
                        sourceTable: '',
                        list: [],
                        loading: false,
                        page: 1,
                        pageSize: 15,
                        total: 0,
                        drawer: false,
                        drawerTitle: '',
                        drawerJson: '',
                    };
                },
                computed: {
                    canRestore() {
                        return permCodes.indexOf('recycle_bin:restore') !== -1;
                    },
                    canPurge() {
                        return permCodes.indexOf('recycle_bin:purge') !== -1;
                    },
                },
                mounted() {
                    neoAxiosSetup(axios);
                    this.fetchList();
                },
                methods: {
                    fetchList() {
                        this.loading = true;
                        axios
                            .get(cfg.listUrl, {
                                params: {
                                    keyword: this.keyword || undefined,
                                    source_table: this.sourceTable || undefined,
                                    page: this.page,
                                    page_size: this.pageSize,
                                },
                            })
                            .then((res) => {
                                const body = res.data || {};
                                if (body.code !== 0) {
                                    ElementPlus.ElMessage.error(body.message || '加载失败');
                                    return;
                                }
                                const d = body.data || {};
                                this.list = d.list || [];
                                const pg = d.pagination || {};
                                this.total = pg.total != null ? pg.total : 0;
                            })
                            .catch(() => ElementPlus.ElMessage.error('网络错误'))
                            .finally(() => {
                                this.loading = false;
                            });
                    },
                    onSearch() {
                        this.page = 1;
                        this.fetchList();
                    },
                    onPageChange(p) {
                        this.page = p;
                        this.fetchList();
                    },
                    onPageSizeChange(sz) {
                        this.pageSize = sz;
                        this.page = 1;
                        this.fetchList();
                    },
                    openPayload(row) {
                        try {
                            this.drawerJson = JSON.stringify(row.payload || {}, null, 2);
                        } catch (e) {
                            this.drawerJson = String(row.payload_preview || '');
                        }
                        this.drawerTitle = '快照 #' + row.id + ' · ' + row.source_table;
                        this.drawer = true;
                    },
                    restore(row) {
                        ElementPlus.ElMessageBox.confirm('确定将该记录恢复到业务表？若主键冲突将失败。', '恢复', {
                            type: 'warning',
                            confirmButtonText: '恢复',
                            cancelButtonText: '取消',
                        })
                            .then(() => {
                                const url = String(cfg.restoreUrlTpl || '').replace('__ID__', row.id);
                                return axios.post(url);
                            })
                            .then((res) => {
                                const body = res.data || {};
                                if (body.code !== 0) {
                                    ElementPlus.ElMessage.error(body.message || '恢复失败');
                                    return;
                                }
                                ElementPlus.ElMessage.success('已恢复');
                                this.fetchList();
                            })
                            .catch((err) => {
                                if (err === 'cancel' || err === 'close') return;
                                const msg =
                                    err.response && err.response.data && err.response.data.message;
                                ElementPlus.ElMessage.error(msg || '恢复失败');
                            });
                    },
                    purge(row) {
                        ElementPlus.ElMessageBox.confirm(
                            '彻底清除将删除本条回收站记录，业务数据不可再通过此处恢复。是否继续？',
                            '彻底清除',
                            { type: 'warning', confirmButtonText: '清除', cancelButtonText: '取消' }
                        )
                            .then(() => {
                                const url = String(cfg.purgeUrlTpl || '').replace('__ID__', row.id);
                                return axios.delete(url);
                            })
                            .then((res) => {
                                const body = res.data || {};
                                if (body.code !== 0) {
                                    ElementPlus.ElMessage.error(body.message || '清除失败');
                                    return;
                                }
                                ElementPlus.ElMessage.success('已清除');
                                this.fetchList();
                            })
                            .catch((err) => {
                                if (err === 'cancel' || err === 'close') return;
                                const msg =
                                    err.response && err.response.data && err.response.data.message;
                                ElementPlus.ElMessage.error(msg || '清除失败');
                            });
                    },
                },
                template: `
                    <div>
                        <div class="neo-rb-hd">回收站管理</div>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;align-items:center;">
                            <el-input v-model="keyword" clearable placeholder="来源表 / 模型类名" style="width:220px" @keyup.enter="onSearch" />
                            <el-input v-model="sourceTable" clearable placeholder="请输入表名" style="width:180px" @keyup.enter="onSearch" />
                            <el-button type="primary" @click="onSearch">查询</el-button>
                        </div>
                        <el-table v-loading="loading" :data="list" stripe style="width:100%" empty-text="暂无数据">
                            <el-table-column prop="id" label="ID" width="72" />
                            <el-table-column prop="source_table" label="来源表" width="140" show-overflow-tooltip />
                            <el-table-column prop="model_class" label="模型类" min-width="220" show-overflow-tooltip />
                            <el-table-column prop="recycled_at" label="进入回收站" width="168" />
                            <el-table-column prop="operator_label" label="操作人" width="140" show-overflow-tooltip />
                            <el-table-column label="操作" width="240" fixed="right">
                                <template #default="{ row }">
                                    <el-button size="small" link type="primary" @click="openPayload(row)">数据预览</el-button>
                                    <el-button v-if="canRestore" size="small" link type="success" @click="restore(row)">恢复</el-button>
                                    <el-button v-if="canPurge" size="small" link type="danger" @click="purge(row)">彻底清除</el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                        <div style="margin-top:16px;display:flex;justify-content:flex-end;">
                            <el-pagination
                                background
                                layout="total, sizes, prev, pager, next"
                                :total="total"
                                :current-page="page"
                                :page-size="pageSize"
                                :page-sizes="[10, 15, 30, 50]"
                                @current-change="onPageChange"
                                @size-change="onPageSizeChange"
                            />
                        </div>
                        <el-drawer v-model="drawer" :title="drawerTitle" size="55%">
                            <div class="neo-rb-json-wrap">
                                <pre class="neo-rb-json" v-text="drawerJson"></pre>
                            </div>
                        </el-drawer>
                    </div>
                `,
            })
                .use(ElementPlus, { locale: zhCn })
                .mount('#app');
        });
    </script>
@endpush
