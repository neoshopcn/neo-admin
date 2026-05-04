@extends('admin.layouts.content')

@section('title', '使用文档')

@php
    $usageDocLinks = [
        ['title' => 'Laravel 12.x 中文文档', 'href' => 'https://learnku.com/docs/laravel/12.x'],
        ['title' => 'Vue 3 中文文档', 'href' => 'https://cn.vuejs.org/guide/introduction.html'],
        ['title' => 'Element Plus 中文文档', 'href' => 'https://element-plus.org/zh-CN/guide/installation'],
        ['title' => 'Axios 中文文档', 'href' => 'https://axios-http.com/zh/docs/intro'],
    ];
@endphp

@push('scripts')
    <script>
        window.__USAGE_DOC_LINKS__ = @json($usageDocLinks);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp } = Vue;
            const zhCn = window.ElementPlusLocaleZhCn || {};
            const docLinks = window.__USAGE_DOC_LINKS__ || [];

            createApp({
                data() {
                    return { docLinks };
                },
                template: `
                    <div style="width:100%;">
                        <el-card shadow="never" style="border:1px solid #ebeef5;">
                            <template #header><span style="font-weight:600;font-size:15px;color:#303133;">Neo Admin 使用说明</span></template>
                            <div style="color:#606266;line-height:1.75;font-size:14px;">
                                <p style="margin:0 0 14px;">
                                    <strong>Neo Admin</strong> 面向后台的<strong>轻量管理端</strong>：服务端 <strong>Laravel 12.x</strong>、PHP ^8.2；内容区 <strong>Vue 3</strong>、<strong>Element Plus</strong>、<strong>Axios</strong>、<strong>@@element-plus/icons-vue</strong>。
                                    以上前端依赖已通过 <code style="background:#f4f4f5;padding:2px 6px;border-radius:4px;font-size:13px;">public/assets/admin-static/</code> 本地化加载，部署时不依赖外网 CDN。
                                </p>
                                <p style="margin:0 0 12px;"><strong>整体结构</strong>：左侧为按角色授权的菜单树；右侧主区域通过 <code style="background:#f4f4f5;padding:2px 6px;border-radius:4px;font-size:13px;">iframe</code> 加载各业务页，切换菜单时<strong>不刷新</strong>整页外壳，仅更新内容框架。</p>
                                <p style="margin:0 0 12px;"><strong>权限</strong>：菜单与接口绑定权限码；无权限不展示菜单，后端中间件同步校验。</p>
                                <p style="margin:0 0 12px;"><strong>工作台</strong>：默认首页，汇总访问端与服务器信息、资源占用、登录日志等；可在头部<strong>最近打开</strong>中快速返回常用页面。</p>
                                <p style="margin:0 0 12px;"><strong>主题</strong>：右上角主题入口可切换侧栏配色，设置保存在本机 <code style="background:#f4f4f5;padding:2px 6px;border-radius:4px;font-size:13px;">localStorage</code>。</p>
                                <p style="margin:0 0 12px;"><strong>最近打开</strong>：头部第二行展示本地记录的页面标签，点击切换内容页，× 可移除记录。</p>
                                <p style="margin:0 0 12px;"><strong>个人信息</strong>：头像悬停展示姓名、角色、个人信息与退出登录入口。</p>
                                <p style="margin:0 0 18px;"><strong>开发扩展</strong>：后台路由在 <code style="background:#f4f4f5;padding:2px 6px;border-radius:4px;font-size:13px;">routes/admin.php</code>，于 <code style="background:#f4f4f5;padding:2px 6px;border-radius:4px;font-size:13px;">bootstrap/app.php</code> 挂载。
                                    新增页面时注册控制器与 <code style="background:#f4f4f5;padding:2px 6px;border-radius:4px;font-size:13px;">/admin/content/...</code> 路径，并在<strong>菜单管理</strong>中配置路径、图标与权限码即可接入侧栏。</p>
                                <div style="border-top:1px solid #ebeef5;padding-top:14px;margin-top:4px;">
                                    <div style="font-weight:600;color:#303133;margin-bottom:10px;font-size:14px;">项目依赖参考文档</div>
                                    <ul style="margin:0;padding-left:20px;line-height:2;">
                                        <li v-for="(row, i) in docLinks" :key="i">
                                            <el-link :href="row.href" target="_blank" rel="noopener noreferrer" type="primary">@{{ row.title }}</el-link>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </el-card>
                    </div>
                `,
            }).use(ElementPlus, { locale: zhCn }).mount('#app');
        });
    </script>
@endpush
