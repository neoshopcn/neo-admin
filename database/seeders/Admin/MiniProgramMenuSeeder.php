<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class MiniProgramMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'miniprogram:list' => '小程序配置-列表',
        'miniprogram:view' => '小程序配置-查看',
        'miniprogram:create' => '小程序配置-新增',
        'miniprogram:edit' => '小程序配置-编辑',
        'miniprogram:delete' => '小程序配置-删除',
        'miniprogram_user:list' => '小程序用户-列表',
        'miniprogram_user:view' => '小程序用户-查看',
        'miniprogram_user:edit' => '小程序用户-编辑',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $folder = AdminSeedSupport::syncMenuFolder(0, '小程序管理', [
            'icon' => 'Cellphone',
            'sort' => 18,
            'status' => 1,
        ]);

        $cfg = AdminSeedSupport::syncMenuByPermissionCode('miniprogram:list', [
            'parent_id' => $folder->id,
            'name' => '小程序配置管理',
            'icon' => 'Setting',
            'path' => '/admin/content/miniprograms',
            'sort' => 10,
            'status' => 1,
            'type' => 1,
        ]);

        foreach ([
            ['miniprogram:create', '小程序配置-新增'],
            ['miniprogram:edit', '小程序配置-编辑'],
            ['miniprogram:delete', '小程序配置-删除'],
            ['miniprogram:view', '小程序配置-查看'],
        ] as [$code, $label]) {
            AdminSeedSupport::syncMenuByPermissionCode($code, [
                'parent_id' => $cfg->id,
                'name' => $label,
                'icon' => null,
                'path' => null,
                'sort' => 10,
                'status' => 1,
                'type' => 2,
            ]);
        }

        $users = AdminSeedSupport::syncMenuByPermissionCode('miniprogram_user:list', [
            'parent_id' => $folder->id,
            'name' => '小程序用户管理',
            'icon' => 'User',
            'path' => '/admin/content/miniprogram-users',
            'sort' => 20,
            'status' => 1,
            'type' => 1,
        ]);

        foreach ([
            ['miniprogram_user:view', '小程序用户-查看'],
            ['miniprogram_user:edit', '小程序用户-编辑'],
        ] as [$code, $label]) {
            AdminSeedSupport::syncMenuByPermissionCode($code, [
                'parent_id' => $users->id,
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
