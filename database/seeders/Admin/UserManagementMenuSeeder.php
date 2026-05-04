<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class UserManagementMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'user:list' => '用户列表',
        'user:create' => '新增用户',
        'user:edit' => '编辑用户',
        'user:delete' => '删除用户',
        'user:view' => '查看用户',
        'user:reset_password' => '重置用户密码',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $systemId = AdminSeedSupport::systemManagementDirectoryId();

        $parent = AdminSeedSupport::syncMenuByPermissionCode('user:list', [
            'parent_id' => $systemId,
            'name' => '用户管理',
            'icon' => 'User',
            'path' => '/admin/content/users',
            'sort' => 10,
            'status' => 1,
            'type' => 1,
        ]);

        foreach ([
            ['user:create', '用户-新增'],
            ['user:edit', '用户-编辑'],
            ['user:delete', '用户-删除'],
            ['user:view', '用户-查看'],
            ['user:reset_password', '用户-重置密码'],
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
