<?php

namespace Database\Seeders;

use Database\Seeders\Admin\AdminDemoRolesAndUsersSeeder;
use Database\Seeders\Admin\AdminSeedSupport;
use Database\Seeders\Admin\AdminSystemDirectorySeeder;
use Database\Seeders\Admin\DashboardMenuSeeder;
use Database\Seeders\Admin\DocumentationMenuSeeder;
use Database\Seeders\Admin\MenuManagementMenuSeeder;
use Database\Seeders\Admin\OperationLogMenuSeeder;
use Database\Seeders\Admin\RecycleBinMenuSeeder;
use Database\Seeders\Admin\ResourceMenuSeeder;
use Database\Seeders\Admin\RoleManagementMenuSeeder;
use Database\Seeders\Admin\UserManagementMenuSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 演示权限、菜单、角色与账号：按模块拆分 Seeder，声明式配置见各子类。
 */
class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            AdminSeedSupport::clearPermissionCache();

            $this->call([
                AdminSystemDirectorySeeder::class,
                DashboardMenuSeeder::class,
                ResourceMenuSeeder::class,
                RecycleBinMenuSeeder::class,
                DocumentationMenuSeeder::class,
                UserManagementMenuSeeder::class,
                RoleManagementMenuSeeder::class,
                MenuManagementMenuSeeder::class,
                OperationLogMenuSeeder::class,
                AdminDemoRolesAndUsersSeeder::class,
            ]);
        });
    }
}
