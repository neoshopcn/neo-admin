@extends('admin.layouts.content')

@section('title', '工作台')

@push('scripts')
    <script>
        window.__DASHBOARD_PAGE__ = @json($dashboardPage);
    </script>
    <style>
        .neo-dash { max-width: 1400px; margin: 0 auto; }
        .neo-dash .neo-card { border: 1px solid #ebeef5; margin-bottom: 16px; }
        .neo-dash .neo-card .el-card__header {
            font-weight: 600;
            font-size: 14px;
            color: #303133;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .neo-welcome-card {
            margin-bottom: 16px;
            border: none !important;
            background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 42%, #faf6ff 100%) !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04), 0 8px 24px rgba(64, 158, 255, 0.08) !important;
            overflow: hidden;
            position: relative;
        }
        .neo-welcome-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: linear-gradient(180deg, var(--el-color-primary) 0%, #79bbff 50%, #b882ff 100%);
        }
        .neo-welcome-card .el-card__body { padding: 22px 22px 22px 26px !important; }
        .neo-welcome-layout {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }
        .neo-welcome-avatar {
            flex-shrink: 0;
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, var(--el-color-primary) 0%, #66b1ff 45%, #a77efc 100%);
            color: #fff;
            box-shadow: 0 6px 16px rgba(64, 158, 255, 0.35);
        }
        .neo-welcome-main { flex: 1; min-width: 0; }
        .neo-welcome-kicker {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #909399;
            letter-spacing: 0.06em;
            text-transform: none;
            margin-bottom: 8px;
        }
        .neo-welcome-kicker-icon { color: var(--el-color-warning); }
        .neo-welcome-heading {
            margin: 0 0 12px;
            font-size: 22px;
            font-weight: 700;
            color: #303133;
            line-height: 1.35;
            letter-spacing: 0.02em;
        }
        .neo-welcome-greeting {
            font-weight: 500;
            color: #606266;
            font-size: 18px;
        }
        .neo-welcome-name {
            background: linear-gradient(90deg, var(--el-color-primary) 0%, #7c5cdb 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            font-size: 22px;
        }
        .neo-welcome-meta { margin-bottom: 12px; }
        .neo-welcome-pill {
            font-weight: 600;
            letter-spacing: 0.03em;
            border: none !important;
        }
        .neo-welcome-pill .el-icon { vertical-align: -3px; margin-right: 4px; }
        .neo-welcome-lead {
            margin: 0;
            font-size: 13px;
            line-height: 1.65;
            color: #606266;
            max-width: 820px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .neo-welcome-lead strong { color: #303133; font-weight: 600; }
        .neo-welcome-lead-icon {
            flex-shrink: 0;
            margin-top: 2px;
            font-size: 16px;
        }
        .neo-welcome-lead.is-self .neo-welcome-lead-icon { color: var(--el-color-primary); }
        .neo-welcome-lead.is-global .neo-welcome-lead-icon { color: var(--el-color-success); }
        @media (max-width: 600px) {
            .neo-welcome-heading { font-size: 18px; }
            .neo-welcome-name { font-size: 18px; }
            .neo-welcome-greeting { font-size: 15px; }
        }
        .neo-guide-steps { padding: 8px 0 4px; max-width: 720px; }
        .neo-guide-steps .el-step__title { font-size: 13px !important; line-height: 1.45; }
        .neo-guide-steps .el-step__description { margin-top: 4px !important; font-size: 12px !important; line-height: 1.55 !important; color: #909399 !important; }
        .neo-stat-grid { margin-bottom: 4px; }
        .neo-stat-tile {
            background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
            border: 1px solid #ebeef5;
            border-radius: 8px;
            padding: 16px 18px;
            height: 100%;
            box-sizing: border-box;
        }
        .neo-stat-tile .neo-stat-label { font-size: 12px; color: #909399; margin-bottom: 8px; letter-spacing: 0.02em; }
        .neo-stat-tile .neo-stat-value { font-size: 26px; font-weight: 600; color: #303133; line-height: 1.2; font-variant-numeric: tabular-nums; }
        .neo-stat-tile .neo-stat-sub { margin-top: 6px; font-size: 12px; color: #c0c4cc; }
        .neo-stat-tile.neo-stat-accent { border-color: rgba(64, 158, 255, 0.35); background: linear-gradient(135deg, rgba(64, 158, 255, 0.06) 0%, #fff 48%); }
        .neo-stat-tile.neo-stat-warn { border-color: rgba(230, 162, 60, 0.35); background: linear-gradient(135deg, rgba(230, 162, 60, 0.06) 0%, #fff 48%); }
        .neo-dash-footer {
            margin-top: 8px;
            padding: 20px 8px 8px;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            text-align: center;
            font-size: 12px;
            line-height: 1.6;
            color: #909399;
            letter-spacing: 0.03em;
            user-select: none;
        }
        .neo-dash-desc-wrap { --neo-desc-label-w: 42%; }
        .neo-dash-desc-wrap .el-descriptions__label {
            width: var(--neo-desc-label-w);
            max-width: var(--neo-desc-label-w);
            color: #909399;
            font-weight: 500;
            vertical-align: middle;
        }
        .neo-dash-desc-wrap .el-descriptions__content {
            width: calc(100% - var(--neo-desc-label-w));
            text-align: right;
            vertical-align: middle;
        }
        .neo-dash-desc-wrap .neo-desc-value-cell {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            min-height: 24px;
        }
        .neo-dash-desc-wrap .neo-desc-tag {
            font-weight: 500;
            letter-spacing: 0.02em;
            border-radius: 999px !important;
            max-width: 100%;
            white-space: normal;
            height: auto !important;
            line-height: 1.45 !important;
            padding: 5px 12px !important;
            justify-content: flex-end;
            text-align: right;
        }
        .neo-dash-desc-wrap .neo-desc-tag.neo-desc-tag--mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp, markRaw } = Vue;
            const zhCn = window.ElementPlusLocaleZhCn || {};
            let guideHidden = false;
            try {
                guideHidden = localStorage.getItem('neo_admin_dashboard_guide_hidden') === '1';
            } catch (e) {}
            const page = markRaw(window.__DASHBOARD_PAGE__ || {});

            const app = createApp({
                data() {
                    return {
                        page,
                        guideHidden,
                        clockTimer: null,
                        clientLang: '',
                        clientPlatform: '',
                        clientCores: '',
                        clientScreen: '',
                        clientTz: '',
                    };
                },
                computed: {
                    onboardingActive() {
                        return (this.page.onboardingSteps || []).length;
                    },
                },
                mounted() {
                    this.clientLang = navigator.language || '—';
                    this.clientPlatform = navigator.platform || '—';
                    this.clientCores = navigator.hardwareConcurrency != null ? String(navigator.hardwareConcurrency) : '—';
                    this.clientScreen = (typeof screen !== 'undefined') ? (screen.width + '×' + screen.height) : '—';
                    try {
                        this.clientTz = Intl.DateTimeFormat().resolvedOptions().timeZone || '—';
                    } catch (e) {
                        this.clientTz = '—';
                    }
                    this.clockTimer = setInterval(() => this.tickClock(), 1000);
                    this.$nextTick(() => this.tickClock());
                },
                unmounted() {
                    if (this.clockTimer) clearInterval(this.clockTimer);
                },
                methods: {
                    tickClock() {
                        const el = this.$refs.dashClockEl;
                        if (!el) return;
                        const d = new Date();
                        const pad = (n) => String(n).padStart(2, '0');
                        el.textContent = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
                            + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
                    },
                    dismissGuide() {
                        this.guideHidden = true;
                        try {
                            localStorage.setItem('neo_admin_dashboard_guide_hidden', '1');
                        } catch (e) {}
                        ElementPlus.ElMessage.success('已隐藏新手指引');
                    },
                },
                template: `
                    <div class="neo-dash">
                        <el-card class="neo-card neo-welcome-card" shadow="never">
                            <div class="neo-welcome-layout">
                                <div class="neo-welcome-avatar" aria-hidden="true">
                                    <el-icon :size="34"><UserFilled /></el-icon>
                                </div>
                                <div class="neo-welcome-main">
                                    <div class="neo-welcome-kicker">
                                        <el-icon class="neo-welcome-kicker-icon" :size="16"><Sunny /></el-icon>
                                        <span>工作台 · 高效协作</span>
                                    </div>
                                    <h2 class="neo-welcome-heading">
                                        <span class="neo-welcome-greeting">欢迎回来，</span><span class="neo-welcome-name">@{{ page.welcomeName || '用户' }}</span>
                                    </h2>
                                    <div class="neo-welcome-meta">
                                        <el-tag v-if="page.statsScoped" type="primary" effect="dark" size="large" round class="neo-welcome-pill">
                                            <el-icon><FolderOpened /></el-icon>
                                            个人数据视图
                                        </el-tag>
                                        <el-tag v-else type="success" effect="dark" size="large" round class="neo-welcome-pill">
                                            <el-icon><DataAnalysis /></el-icon>
                                            全局汇总视图
                                        </el-tag>
                                    </div>
                                    <p class="neo-welcome-lead" :class="page.statsScoped ? 'is-self' : 'is-global'">
                                        <template v-if="page.statsScoped">
                                            <el-icon class="neo-welcome-lead-icon"><InfoFilled /></el-icon>
                                            <span>下方「数据概览」与回收站统计仅包含<strong>您上传的资源</strong>与<strong>您删除的回收站条目</strong>；登录日志亦仅展示与当前账号相关的记录。</span>
                                        </template>
                                        <template v-else>
                                            <el-icon class="neo-welcome-lead-icon"><CircleCheckFilled /></el-icon>
                                            <span>您正以<strong>超级管理员</strong>身份使用工作台：统计与回收站为<strong>全系统汇总</strong>，登录日志展示所有账号的最近尝试。</span>
                                        </template>
                                    </p>
                                </div>
                            </div>
                        </el-card>

                        <el-card v-if="!guideHidden" class="neo-card" shadow="never">
                            <template #header>
                                <span>新手指引</span>
                                <el-button type="primary" link size="small" @click="dismissGuide">不再显示</el-button>
                            </template>
                            <el-steps class="neo-guide-steps" direction="vertical" :active="onboardingActive" finish-status="success">
                                <el-step v-for="(step, i) in (page.onboardingSteps || [])" :key="'st'+i" :title="step.title" :description="step.description" />
                            </el-steps>
                        </el-card>

                        <el-card class="neo-card" shadow="never">
                            <template #header>数据概览</template>
                            <el-row class="neo-stat-grid" :gutter="16">
                                <el-col :xs="12" :sm="6" style="margin-bottom:16px">
                                    <div class="neo-stat-tile neo-stat-accent">
                                        <div class="neo-stat-label">资源文件数</div>
                                        <div class="neo-stat-value">@{{ page.resourceStats?.total_count ?? 0 }}</div>
                                        <div class="neo-stat-sub">条记录</div>
                                    </div>
                                </el-col>
                                <el-col :xs="12" :sm="6" style="margin-bottom:16px">
                                    <div class="neo-stat-tile">
                                        <div class="neo-stat-label">资源占用存储</div>
                                        <div class="neo-stat-value" style="font-size:20px">@{{ page.resourceStats?.total_bytes_label || '0 B' }}</div>
                                        <div class="neo-stat-sub">范围内文件体积合计</div>
                                    </div>
                                </el-col>
                                <el-col :xs="12" :sm="6" style="margin-bottom:16px">
                                    <div class="neo-stat-tile">
                                        <div class="neo-stat-label">资源状态</div>
                                        <div class="neo-stat-value" style="font-size:18px">
                                            <span style="color:#67c23a">@{{ page.resourceStats?.active_count ?? 0 }}</span>
                                            <span style="color:#909399;font-weight:500;font-size:14px"> / </span>
                                            <span style="color:#e6a23c">@{{ page.resourceStats?.disabled_count ?? 0 }}</span>
                                        </div>
                                        <div class="neo-stat-sub">启用 / 停用</div>
                                    </div>
                                </el-col>
                                <el-col :xs="12" :sm="6" style="margin-bottom:16px">
                                    <div class="neo-stat-tile neo-stat-warn">
                                        <div class="neo-stat-label">回收站条目</div>
                                        <div class="neo-stat-value">@{{ page.recycleBinStats?.total_count ?? 0 }}</div>
                                        <div class="neo-stat-sub">待恢复或清理</div>
                                    </div>
                                </el-col>
                            </el-row>
                        </el-card>

                        <el-row :gutter="16">
                            <el-col :xs="24" :lg="12">
                                <el-card class="neo-card" shadow="never">
                                    <template #header>服务器信息</template>
                                    <el-descriptions class="neo-dash-desc-wrap" :column="1" border size="small">
                                        <el-descriptions-item v-for="(row, i) in (page.serverInfo || [])" :key="'s'+i" :label="row.label">
                                            <div class="neo-desc-value-cell">
                                                <el-tag
                                                    effect="plain"
                                                    size="small"
                                                    class="neo-desc-tag"
                                                    :type="i === 1 ? 'success' : (i === 2 ? 'warning' : (i === 0 ? 'info' : 'primary'))"
                                                >@{{ row.value }}</el-tag>
                                            </div>
                                        </el-descriptions-item>
                                    </el-descriptions>
                                    <!-- 项目必需扩展
                                    <div style="margin-top:14px;font-weight:600;font-size:13px;color:#303133;margin-bottom:8px;">项目必需扩展</div>
                                    <el-table :data="page.extensionChecks || []" size="small" border stripe style="width:100%">
                                        <el-table-column prop="name" label="扩展" min-width="120" />
                                        <el-table-column label="状态" width="88">
                                            <template #default="scope">
                                                <el-tag :type="scope.row.loaded ? 'success' : 'danger'" size="small">@{{ scope.row.loaded ? '已加载' : '未加载' }}</el-tag>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                    -->
                                </el-card>
                            </el-col>
                            <el-col :xs="24" :lg="12">
                                <el-card class="neo-card" shadow="never">
                                    <template #header>客户端信息</template>
                                    <el-descriptions class="neo-dash-desc-wrap" :column="1" border size="small">
                                        <el-descriptions-item label="访问 IP">
                                            <div class="neo-desc-value-cell">
                                                <el-tag type="primary" effect="dark" size="small" round class="neo-desc-tag neo-desc-tag--mono">@{{ page.clientIp || '—' }}</el-tag>
                                            </div>
                                        </el-descriptions-item>
                                        <el-descriptions-item label="当前时间">
                                            <div class="neo-desc-value-cell">
                                                <el-tag type="info" effect="plain" size="small" class="neo-desc-tag neo-desc-tag--mono"><span ref="dashClockEl">—</span></el-tag>
                                            </div>
                                        </el-descriptions-item>
                                        <el-descriptions-item label="浏览器语言">
                                            <div class="neo-desc-value-cell">
                                                <el-tag effect="plain" size="small" class="neo-desc-tag">@{{ clientLang }}</el-tag>
                                            </div>
                                        </el-descriptions-item>
                                        <el-descriptions-item label="系统平台">
                                            <div class="neo-desc-value-cell">
                                                <el-tag type="warning" effect="plain" size="small" class="neo-desc-tag">@{{ clientPlatform }}</el-tag>
                                            </div>
                                        </el-descriptions-item>
                                        <el-descriptions-item label="逻辑核心数">
                                            <div class="neo-desc-value-cell">
                                                <el-tag type="success" effect="plain" size="small" class="neo-desc-tag neo-desc-tag--mono">@{{ clientCores }}</el-tag>
                                            </div>
                                        </el-descriptions-item>
                                        <el-descriptions-item label="屏幕分辨率">
                                            <div class="neo-desc-value-cell">
                                                <el-tag effect="plain" size="small" class="neo-desc-tag neo-desc-tag--mono">@{{ clientScreen }}</el-tag>
                                            </div>
                                        </el-descriptions-item>
                                        <el-descriptions-item label="时区">
                                            <div class="neo-desc-value-cell">
                                                <el-tag type="info" effect="plain" size="small" class="neo-desc-tag">@{{ clientTz }}</el-tag>
                                            </div>
                                        </el-descriptions-item>
                                    </el-descriptions>
                                </el-card>
                            </el-col>
                        </el-row>

                        <el-card class="neo-card" shadow="never">
                            <template #header>登录日志</template>
                            <el-table :data="page.loginLogs || []" size="small" border stripe style="width:100%">
                                <el-table-column prop="created_at" label="时间" width="168" />
                                <el-table-column prop="username" label="账号" width="120" />
                                <el-table-column prop="name" label="姓名" width="100">
                                    <template #default="scope">@{{ scope.row.name || '—' }}</template>
                                </el-table-column>
                                <el-table-column prop="ip" label="IP" width="130" />
                                <el-table-column label="结果" width="88">
                                    <template #default="scope">
                                        <el-tag :type="scope.row.success ? 'success' : 'danger'" size="small">@{{ scope.row.success ? '成功' : '失败' }}</el-tag>
                                    </template>
                                </el-table-column>
                                <el-table-column prop="user_agent_short" label="UA 摘要" min-width="220" show-overflow-tooltip />
                            </el-table>
                            <div v-if="!(page.loginLogs && page.loginLogs.length)" style="padding:12px;color:#909399;font-size:13px;">暂无记录，成功或失败的登录尝试都会写入日志。</div>
                        </el-card>

                        <div v-if="page.copyrightFooter" class="neo-dash-footer" role="contentinfo">@{{ page.copyrightFooter }}</div>
                    </div>
                `,
            });
            app.use(ElementPlus, { locale: zhCn });
            const icons = window.ElementPlusIconsVue || {};
            for (const [key, comp] of Object.entries(icons)) {
                app.component(key, comp);
            }
            app.mount('#app');
        });
    </script>
@endpush
