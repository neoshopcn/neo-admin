<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** 当前用户资料 */
class ProfileController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.content.profile', [
            'profilePage' => [
                'uploadUrl' => url('/admin/api/upload'),
                'resourcesListUrl' => url('/admin/api/resources'),
                'storagePublicBase' => rtrim((string) config('filesystems.disks.public.url'), '/'),
                'meUrl' => url('/admin/api/me'),
            ],
        ]);
    }
}
