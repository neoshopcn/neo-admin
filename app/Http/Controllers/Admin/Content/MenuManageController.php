<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** 菜单维护页 */
class MenuManageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.content.menus', [
            'treeUrl' => url('/admin/api/menus/tree'),
            'permissionsUrl' => url('/admin/api/permissions/options'),
        ]);
    }
}
