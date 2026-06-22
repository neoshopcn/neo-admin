<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class ConfigCenterMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'system_config:list' => '系统配置列表',
        'system_config:edit' => '系统配置编辑',
        'api_config:list' => '接口配置列表',
        'api_config:edit' => '接口配置编辑',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $configCenterId = AdminSeedSupport::configCenterDirectoryId();

        $systemMenu = AdminSeedSupport::syncMenuByPermissionCode('system_config:list', [
            'parent_id' => $configCenterId,
            'name' => '系统配置',
            'icon' => 'Setting',
            'path' => '/admin/content/config/system',
            'sort' => 10,
            'status' => 1,
            'type' => 1,
        ]);

        AdminSeedSupport::syncMenuByPermissionCode('system_config:edit', [
            'parent_id' => $systemMenu->id,
            'name' => '系统配置-保存',
            'icon' => null,
            'path' => null,
            'sort' => 10,
            'status' => 1,
            'type' => 2,
        ]);

        $apiMenu = AdminSeedSupport::syncMenuByPermissionCode('api_config:list', [
            'parent_id' => $configCenterId,
            'name' => '接口配置',
            'icon' => 'Connection',
            'path' => '/admin/content/config/api',
            'sort' => 20,
            'status' => 1,
            'type' => 1,
        ]);

        AdminSeedSupport::syncMenuByPermissionCode('api_config:edit', [
            'parent_id' => $apiMenu->id,
            'name' => '接口配置-保存',
            'icon' => null,
            'path' => null,
            'sort' => 10,
            'status' => 1,
            'type' => 2,
        ]);
    }
}
