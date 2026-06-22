<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** 文档示例页 */
class DocumentationController extends Controller
{
    public function usage(): View
    {
        return view('admin.content.doc.usage');
    }

    public function charts(): View
    {
        return view('admin.content.doc.charts');
    }

    public function sysinfo(): View
    {
        return view('admin.content.doc.sysinfo', [
            'server' => [
                'PHP 版本' => PHP_VERSION,
                '操作系统' => PHP_OS_FAMILY.' ('.php_uname('s').' '.php_uname('r').')',
                'Web 服务' => $_SERVER['SERVER_SOFTWARE'] ?? '—',
                '应用时区' => config('app.timezone'),
                'Laravel 版本' => app()->version(),
                '内存限制' => ini_get('memory_limit'),
                '最大上传' => ini_get('upload_max_filesize'),
            ],
            'project' => [
                '应用名称' => config('app.name'),
                '运行环境' => config('app.env'),
                '调试模式' => config('app.debug') ? '开启' : '关闭',
                'APP_URL' => config('app.url'),
                '默认语言' => config('app.locale'),
                '项目根目录' => base_path(),
                '缓存驱动' => config('cache.default'),
                '会话驱动' => config('session.driver'),
                '数据库连接' => config('database.default'),
            ],
        ]);
    }

    public function richtext(): View
    {
        return view('admin.content.doc.richtext', [
            'richtextPage' => [
                'uploadUrl' => url('/admin/api/upload'),
                'resourcesListUrl' => url('/admin/api/resources'),
                'storagePublicBase' => rtrim((string) config('filesystems.disks.public.url'), '/'),
            ],
        ]);
    }

    public function uploadDemo(): View
    {
        return view('admin.content.doc.upload-demo', [
            'uploadPage' => [
                'uploadUrl' => url('/admin/api/upload'),
                'resourcesListUrl' => url('/admin/api/resources'),
                'storagePublicBase' => rtrim((string) config('filesystems.disks.public.url'), '/'),
            ],
        ]);
    }

    public function tableDemo(): View
    {
        return view('admin.content.doc.table-demo', [
            'tableCfg' => [
                'title' => '表格操作示例',
                'listUrl' => url('/admin/api/doc/table-demo'),
                'rowKey' => 'id',
                'selectionEnabled' => true,
                'selectionReserve' => false,
                'batchActions' => [
                    ['key' => 'demoBatch', 'label' => '批量操作演示1'],
                    ['key' => 'demoBatch', 'label' => '批量操作演示2'],
                ],
                'demoPatchUrl' => url('/admin/api/doc/table-demo/patch'),
                'columns' => [
                    ['prop' => 'id', 'label' => 'ID', 'width' => 72],
                    [
                        'prop' => 'sort_order',
                        'label' => '排序',
                        'width' => 136,
                        'inlineNumber' => true,
                        'inlineMin' => 0,
                        'inlineMax' => 99999,
                        'inlineStep' => 1,
                        'inlinePrecision' => 0,
                    ],
                    ['prop' => 'title', 'label' => '标题', 'minWidth' => 160],
                    [
                        'prop' => 'status',
                        'label' => '状态',
                        'width' => 124,
                        'tag' => true,
                        'inlineSelect' => true,
                        'inlineSelectIcon' => 'EditPen',
                        'inlineSelectOptions' => [
                            ['label' => '启用', 'value' => 1],
                            ['label' => '禁用', 'value' => 0],
                        ],
                    ],
                    ['prop' => 'updated_at', 'label' => '更新时间', 'minWidth' => 168],
                ],
                'filters' => [
                    ['type' => 'input', 'key' => 'keyword', 'placeholder' => '筛选标题关键字'],
                    [
                        'type' => 'select',
                        'key' => 'status',
                        'placeholder' => '状态',
                        'clearable' => true,
                        'options' => [['label' => '启用', 'value' => 1], ['label' => '禁用', 'value' => 0]],
                    ],
                    ['type' => 'daterange', 'startKey' => 'created_from', 'endKey' => 'created_to'],
                ],
                'hideActions' => true,
            ],
        ]);
    }
}
