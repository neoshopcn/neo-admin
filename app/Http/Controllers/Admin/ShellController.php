<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** 后台外壳布局 */
class ShellController extends Controller
{
    public function index(): View
    {
        return view('admin.shell', [
            'adminThemes' => config('admin.themes'),
            'adminDefaultTheme' => config('admin.default_theme'),
        ]);
    }
}
