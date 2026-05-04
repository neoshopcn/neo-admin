<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionOptionsController extends Controller
{
    use ApiResponse;

    public function options(): JsonResponse
    {
        $rows = Permission::query()->orderBy('id')->get(['id', 'code', 'name']);

        return $this->ok($rows);
    }
}
