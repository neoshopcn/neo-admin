@extends('admin.layouts.content')

@section('title', $title)

@push('scripts')
    <style>
        .neo-config-card {
            background: #fff;
            border-radius: 4px;
            min-height: calc(100vh - 32px);
        }
        .neo-config-top {
            background: #fff;
            padding-bottom: 20px;
        }
        .neo-config-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            background: #fff;
            border-bottom: 1px solid #e4e7ed;
        }
        .neo-config-title {
            font-size: 18px;
            font-weight: 600;
            color: #303133;
            line-height: 1.4;
        }
        .neo-config-tabs.el-tabs {
            background: #fff;
        }
        .neo-config-tabs > .el-tabs__header {
            margin: 0;
            padding: 0 24px;
            background: #fff;
            border-bottom: 1px solid #e4e7ed;
        }
        .neo-config-tabs > .el-tabs__header .el-tabs__nav-wrap::after {
            display: none;
        }
        .neo-config-tabs > .el-tabs__header .el-tabs__item {
            height: 44px;
            line-height: 44px;
            padding: 0 20px;
            font-size: 14px;
            border: none !important;
            background: transparent !important;
        }
        .neo-config-tabs > .el-tabs__header .el-tabs__active-bar {
            height: 2px;
        }
        .neo-config-tabs > .el-tabs__content {
            padding: 24px;
            background: #fff;
        }
        .neo-config-section-tabs.el-tabs--card {
            max-width: 820px;
            margin-bottom: 4px;
        }
        .neo-config-section-tabs.el-tabs--card > .el-tabs__header {
            margin: 0;
            border-bottom: none;
            background: transparent;
        }
        .neo-config-section-tabs.el-tabs--card > .el-tabs__header .el-tabs__nav {
            border: none;
        }
        .neo-config-section-tabs.el-tabs--card > .el-tabs__header .el-tabs__item {
            height: 36px;
            line-height: 36px;
            padding: 0 18px;
            font-size: 13px;
            color: #606266;
            border: 1px solid #e4e7ed;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            margin-right: 6px;
            background: #f5f7fa;
            transition: color 0.2s, background 0.2s;
        }
        .neo-config-section-tabs.el-tabs--card > .el-tabs__header .el-tabs__item.is-active {
            color: var(--el-color-primary);
            background: #fff;
            border-bottom-color: #fff;
            font-weight: 500;
        }
        .neo-config-section-tabs.el-tabs--card > .el-tabs__header .el-tabs__item:first-child {
            border-left: 1px solid #e4e7ed;
        }
        .neo-config-section-tabs.el-tabs--card > .el-tabs__content {
            border: 1px solid #e4e7ed;
            border-radius: 0 4px 4px 4px;
            padding: 20px 20px 4px;
            background: #fff;
        }
        .neo-config-save {
            margin-top: 8px;
            padding-top: 20px;
            max-width: 760px;
        }
    </style>
    <script>window.__NEO_CONFIG__ = @json($neo);</script>
    <script src="{{ asset('js/config-center.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            neoAxiosSetup(axios);
            mountConfigCenter('#app', window.__NEO_CONFIG__);
        });
    </script>
@endpush
