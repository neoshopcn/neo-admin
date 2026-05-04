<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class RoleManagementMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'role:list' => '角色列表',
        'role:create' => '新增角色',
        'role:edit' => '编辑角色',
        'role:delete' => '删除角色',
        'role:assign_menu' => '角色授权菜单',
        'role:view' => '查看角色',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $systemId = AdminSeedSupport::systemManagementDirectoryId();

        $parent = AdminSeedSupport::syncMenuByPermissionCode('role:list', [
            'parent_id' => $systemId,
            'name' => '角色管理',
            'icon' => 'Key',
            'path' => '/admin/content/roles',
            'sort' => 20,
            'status' => 1,
            'type' => 1,
        ]);

        foreach ([
            ['role:create', '角色-新增'],
            ['role:edit', '角色-编辑'],
            ['role:delete', '角色-删除'],
            ['role:assign_menu', '角色-菜单授权'],
            ['role:view', '角色-查看'],
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
