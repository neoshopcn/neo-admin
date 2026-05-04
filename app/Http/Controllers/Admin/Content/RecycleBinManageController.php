<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** 回收站管理页 */
class RecycleBinManageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.content.recycle-bin', [
            'recycleBinPage' => [
                'listUrl' => url('/admin/api/recycle-bin/items'),
                'restoreUrlTpl' => url('/admin/api/recycle-bin/items/__ID__/restore'),
                'purgeUrlTpl' => url('/admin/api/recycle-bin/items/__ID__'),
            ],
        ]);
    }
}
