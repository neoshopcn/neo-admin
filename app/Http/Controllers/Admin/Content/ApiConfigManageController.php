<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\ConfigGroup;
use Illuminate\Http\Request;

class ApiConfigManageController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('admin.content.config-center', [
            'title' => '接口配置',
            'neo' => [
                'title' => '接口配置',
                'page' => ConfigGroup::PAGE_API,
                'loadUrl' => url('/admin/api/config-center/api'),
                'saveUrl' => url('/admin/api/config-center/api/values'),
                'perms' => [
                    'edit' => 'api_config:edit',
                ],
            ],
        ]);
    }
}
