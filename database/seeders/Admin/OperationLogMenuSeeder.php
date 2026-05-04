<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class OperationLogMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'log:list' => '操作日志',
        'log:delete' => '操作日志-删除',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $systemId = AdminSeedSupport::systemManagementDirectoryId();

        $parent = AdminSeedSupport::syncMenuByPermissionCode('log:list', [
            'parent_id' => $systemId,
            'name' => '操作日志',
            'icon' => 'Document',
            'path' => '/admin/content/logs',
            'sort' => 40,
            'status' => 1,
            'type' => 1,
        ]);

        AdminSeedSupport::syncMenuByPermissionCode('log:delete', [
            'parent_id' => $parent->id,
            'name' => '操作日志-删除',
            'icon' => null,
            'path' => null,
            'sort' => 10,
            'status' => 1,
            'type' => 2,
        ]);
    }
}
