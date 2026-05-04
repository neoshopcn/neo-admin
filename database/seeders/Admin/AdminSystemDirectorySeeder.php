<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

/** 系统管理根目录（无权限） */
class AdminSystemDirectorySeeder extends Seeder
{
    public function run(): void
    {
        AdminSeedSupport::syncMenuFolder(0, '系统管理', [
            'icon' => 'Setting',
            'sort' => 20,
            'status' => 1,
        ]);
    }
}
