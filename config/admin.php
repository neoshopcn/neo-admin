<?php

return [
    /*
    |--------------------------------------------------------------------------
    | title
    |--------------------------------------------------------------------------
    |
    | 浏览器标签页标题（文档 title）。
    |
    */
    'title' => env('ADMIN_TITLE', 'NeoAdmin后台管理'),

    /*
    |--------------------------------------------------------------------------
    | brand_name
    |--------------------------------------------------------------------------
    |
    | 侧栏品牌名；与 Logo 图片 alt 一致。
    |
    */
    'brand_name' => env('ADMIN_BRAND_NAME', 'NeoAdmin'),

    /*
    |--------------------------------------------------------------------------
    | sidebar_width_collapsed
    |--------------------------------------------------------------------------
    |
    | 侧栏折叠宽度（el-aside）；须含单位。
    |
    */
    'sidebar_width_collapsed' => env('ADMIN_SIDEBAR_WIDTH_COLLAPSED', '64px'),

    /*
    |--------------------------------------------------------------------------
    | sidebar_width_expanded
    |--------------------------------------------------------------------------
    |
    | 侧栏展开宽度（el-aside）；须含单位。
    |
    */
    'sidebar_width_expanded' => env('ADMIN_SIDEBAR_WIDTH_EXPANDED', '220px'),

    /*
    |--------------------------------------------------------------------------
    | 默认后台主题
    |--------------------------------------------------------------------------
    */
    'default_theme' => env('ADMIN_THEME', 'blue'),

    /*
    |--------------------------------------------------------------------------
    | 页脚版权
    |--------------------------------------------------------------------------
    |
    | copyright_footer_enabled：是否展示版权行
    | copyright_footer_name：展示名称
    |
    */
    'copyright_footer_enabled' => env('ADMIN_COPYRIGHT_FOOTER_ENABLED', true),
    'copyright_footer_name' => env('ADMIN_COPYRIGHT_FOOTER_NAME', 'NeoAdmin'),

    /*
    |--------------------------------------------------------------------------
    | 主题配色 themes
    |--------------------------------------------------------------------------
    |
    | primary：Element Plus 主色
    | sidebar_*：侧栏配色
    | sidebar_hover_text：可选，悬停菜单文字色
    |
    */
    'themes' => [
        'blue' => [
            'label' => '科技蓝',
            'primary' => '#409EFF',
            'sidebar_bg' => '#1f2d3d',
            'sidebar_logo_bg' => '#1b2735',
            'sidebar_text' => '#bfcbd9',
            'sidebar_active' => '#409EFF',
            'sidebar_hover_bg' => 'rgba(255,255,255,0.06)',
        ],
        'sky' => [
            'label' => '天空蓝',
            'primary' => '#38b6ff',
            'sidebar_bg' => '#1a5f8a',
            'sidebar_logo_bg' => '#154d73',
            'sidebar_text' => '#d8eef9',
            'sidebar_active' => '#7dd3fc',
            'sidebar_hover_bg' => 'rgba(255,255,255,0.12)',
            'sidebar_hover_text' => '#ffffff',
        ],
        'cyan' => [
            'label' => '清新青',
            'primary' => '#13c2c2',
            'sidebar_bg' => '#103438',
            'sidebar_logo_bg' => '#0c2d31',
            'sidebar_text' => '#bfecec',
            'sidebar_active' => '#36cfc9',
            'sidebar_hover_bg' => 'rgba(255,255,255,0.06)',
        ],
        'purple' => [
            'label' => '典雅紫',
            'primary' => '#722ed1',
            'sidebar_bg' => '#201336',
            'sidebar_logo_bg' => '#1a0f2e',
            'sidebar_text' => '#d3adf7',
            'sidebar_active' => '#b37feb',
            'sidebar_hover_bg' => 'rgba(255,255,255,0.06)',
        ],
        'green' => [
            'label' => '生机绿',
            'primary' => '#52c41a',
            'sidebar_bg' => '#163419',
            'sidebar_logo_bg' => '#122b14',
            'sidebar_text' => '#d9f7be',
            'sidebar_active' => '#73d13d',
            'sidebar_hover_bg' => 'rgba(255,255,255,0.06)',
        ],
        'orange' => [
            'label' => '活力橙',
            'primary' => '#fa8c16',
            'sidebar_bg' => '#3b2414',
            'sidebar_logo_bg' => '#331f11',
            'sidebar_text' => '#ffd591',
            'sidebar_active' => '#ffa940',
            'sidebar_hover_bg' => 'rgba(255,255,255,0.06)',
        ],
        'crimson' => [
            'label' => '石榴红',
            'primary' => '#f5222d',
            'sidebar_bg' => '#30171c',
            'sidebar_logo_bg' => '#281318',
            'sidebar_text' => '#ffccc7',
            'sidebar_active' => '#ff7875',
            'sidebar_hover_bg' => 'rgba(255,255,255,0.06)',
        ],
    ],
];
