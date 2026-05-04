<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class DocumentationMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'doc:usage' => '文档-使用文档',
        'doc:charts' => '文档-图表示例',
        'doc:sysinfo' => '文档-系统信息',
        'doc:richtext' => '文档-富文本示例',
        'doc:upload_demo' => '文档-文件上传示例',
        'doc:table_demo' => '文档-表格操作示例',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $docRoot = AdminSeedSupport::syncMenuFolder(0, '文档示例', [
            'icon' => 'Reading',
            'sort' => 30,
            'status' => 1,
        ]);

        foreach ([
            ['doc:usage', '使用文档', 'Document', '/admin/content/doc/usage', 10],
            ['doc:charts', '图表示例', 'DataAnalysis', '/admin/content/doc/charts', 20],
            ['doc:sysinfo', '系统信息', 'Monitor', '/admin/content/doc/sysinfo', 30],
            ['doc:richtext', '富文本示例', 'EditPen', '/admin/content/doc/richtext', 40],
            ['doc:upload_demo', '文件上传示例', 'Upload', '/admin/content/doc/upload-demo', 50],
            ['doc:table_demo', '表格操作示例', 'Grid', '/admin/content/doc/table-demo', 60],
        ] as [$code, $name, $icon, $path, $sort]) {
            AdminSeedSupport::syncMenuByPermissionCode($code, [
                'parent_id' => $docRoot->id,
                'name' => $name,
                'icon' => $icon,
                'path' => $path,
                'sort' => $sort,
                'status' => 1,
                'type' => 1,
            ]);
        }
    }
}
