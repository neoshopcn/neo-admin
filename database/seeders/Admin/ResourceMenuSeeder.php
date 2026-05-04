<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class ResourceMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'resource:list' => '资源列表',
        'resource:create' => '新增资源',
        'resource:edit' => '编辑资源',
        'resource:delete' => '删除资源',
        'resource:view' => '查看资源',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $parent = AdminSeedSupport::syncMenuByPermissionCode('resource:list', [
            'parent_id' => 0,
            'name' => '资源管理',
            'icon' => 'FolderOpened',
            'path' => '/admin/content/resources',
            'sort' => 15,
            'status' => 1,
            'type' => 1,
        ]);

        foreach ([
            ['resource:create', '资源-新增'],
            ['resource:edit', '资源-编辑'],
            ['resource:delete', '资源-删除'],
            ['resource:view', '资源-查看'],
        ] as [$code, $label]) {
            AdminSeedSupport::syncMenuByPermissionCode($code, [
                'parent_id' => $parent->id,
                'name' => $label,
                'icon' => null,
                'path' => null,
                'sort' => 10,
                'status' => 1,
                'type' => 2,
            ]);
        }
    }
}
