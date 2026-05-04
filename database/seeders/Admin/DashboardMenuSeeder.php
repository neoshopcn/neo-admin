<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class DashboardMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'dashboard:view' => '访问工作台',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        AdminSeedSupport::syncMenuByPermissionCode('dashboard:view', [
            'parent_id' => 0,
            'name' => '工作台',
            'icon' => 'Odometer',
            'path' => '/admin/content/dashboard',
            'sort' => 10,
            'status' => 1,
            'type' => 1,
        ]);
    }
}
