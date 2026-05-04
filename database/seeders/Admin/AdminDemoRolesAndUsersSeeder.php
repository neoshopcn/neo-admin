<?php

namespace Database\Seeders\Admin;

use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 演示角色、账号及 role_menu（幂等 sync）。
 */
class AdminDemoRolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        $super = Role::query()->updateOrCreate(
            ['code' => 'super_admin'],
            ['name' => '超级管理员', 'status' => 1]
        );

        $operator = Role::query()->updateOrCreate(
            ['code' => 'operator'],
            ['name' => '演示运营（只读用户）', 'status' => 1]
        );

        $allMenuIds = Menu::query()->pluck('id')->all();
        $super->menus()->sync($allMenuIds);

        $opCodes = [
            'dashboard:view',
            'user:list',
            'user:view',
            'resource:list',
            'doc:usage',
            'doc:charts',
            'doc:sysinfo',
            'doc:richtext',
            'doc:upload_demo',
            'doc:table_demo',
        ];
        $opMenuIds = Menu::query()->whereIn('permission_code', $opCodes)->pluck('id')->all();
        $opMenuIds = AdminSeedSupport::expandMenuAncestors($opMenuIds);
        $operator->menus()->sync($opMenuIds);

        $u1 = User::query()->firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => '超级管理员',
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=neo-admin',
                'email' => null,
                'password' => 'admin123',
                'status' => 1,
            ]
        );
        $u1->roles()->sync([$super->id]);

        $u2 = User::query()->firstOrCreate(
            ['username' => 'operator'],
            [
                'name' => '演示运营',
                'avatar' => 'https://api.dicebear.com/7.x/open-peeps/svg?seed=operator',
                'email' => null,
                'password' => 'operator123',
                'status' => 1,
            ]
        );
        $u2->roles()->sync([$operator->id]);
    }
}
