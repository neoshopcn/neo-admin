<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class RecycleBinMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'recycle_bin:list' => '回收站列表',
        'recycle_bin:restore' => '回收站恢复',
        'recycle_bin:purge' => '回收站彻底清除',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $root = AdminSeedSupport::syncMenuByPermissionCode('recycle_bin:list', [
            'parent_id' => 0,
            'name' => '回收站管理',
            'icon' => 'Delete',
            'path' => '/admin/content/recycle-bin',
            'sort' => 17,
            'status' => 1,
            'type' => 1,
        ]);

        foreach ([
            ['recycle_bin:restore', '回收站-恢复'],
            ['recycle_bin:purge', '回收站-彻底清除'],
        ] as [$code, $label]) {
            AdminSeedSupport::syncMenuByPermissionCode($code, [
                'parent_id' => $root->id,
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
