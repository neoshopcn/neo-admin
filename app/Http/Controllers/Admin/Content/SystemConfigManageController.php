<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\ConfigGroup;
use Illuminate\Http\Request;

class SystemConfigManageController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('admin.content.config-center', [
            'title' => '系统配置',
            'neo' => [
                'title' => '系统配置',
                'page' => ConfigGroup::PAGE_SYSTEM,
                'loadUrl' => url('/admin/api/config-center/system'),
                'saveUrl' => url('/admin/api/config-center/system/values'),
                'perms' => [
                    'edit' => 'system_config:edit',
                ],
            ],
        ]);
    }
}
