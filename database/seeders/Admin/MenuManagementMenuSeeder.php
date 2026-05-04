<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

/** 后台「菜单管理」导航（非 Laravel Menu 模型歧义） */
class MenuManagementMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'menu:list' => '菜单列表',
        'menu:create' => '新增菜单',
        'menu:edit' => '编辑菜单',
        'menu:delete' => '删除菜单',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $systemId = AdminSeedSupport::systemManagementDirectoryId();

        $parent = AdminSeedSupport::syncMenuByPermissionCode('menu:list', [
            'parent_id' => $systemId,
            'name' => '菜单管理',
            'icon' => 'Menu',
            'path' => '/admin/content/menus',
            'sort' => 30,
            'status' => 1,
            'type' => 1,
        ]);

        foreach ([
            ['menu:create', '菜单-新增'],
            ['menu:edit', '菜单-编辑'],
            ['menu:delete', '菜单-删除'],
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
