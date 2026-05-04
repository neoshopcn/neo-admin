<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Admin\Concerns\AppliesListFilters;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use ApiResponse;
    use AppliesListFilters;

    public function options(): JsonResponse
    {
        $rows = Role::query()->where('status', 1)->orderBy('id')->get(['id', 'name']);

        return $this->ok($rows);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Role::query()->orderByDesc('id');
        $this->applyKeyword($query, $request, ['name', 'code']);
        $this->applyExact($query, $request, 'status', 'status');
        $this->applyDateRange($query, $request, 'created_from', 'created_to', 'created_at');

        $pageSize = min(100, max(1, (int) $request->input('page_size', 15)));

        return $this->paginate($query->paginate($pageSize));
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::query()->find($id);
        if (! $role) {
            return $this->fail('记录不存在', 404, 404);
        }

        $menuIds = DB::table('role_menu')->where('role_id', $id)->pluck('menu_id')->all();

        return $this->ok(array_merge($role->toArray(), [
            'menu_ids' => array_map('intval', $menuIds),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'code' => ['required', 'string', 'max:64', Rule::unique('roles', 'code')],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $role = Role::query()->create([
            'name' => $data['name'],
            'code' => $data['code'],
            'status' => (int) $data['status'],
        ]);

        return $this->ok(['id' => $role->id]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::query()->find($id);
        if (! $role) {
            return $this->fail('记录不存在', 404, 404);
        }

        if ($role->code === 'super_admin') {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:64'],
                'status' => ['required', 'integer', 'in:0,1'],
            ]);
            $role->update([
                'name' => $data['name'],
                'status' => (int) $data['status'],
            ]);

            return $this->ok(true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'code' => ['required', 'string', 'max:64', Rule::unique('roles', 'code')->ignore($role->id)],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $role->update([
            'name' => $data['name'],
            'code' => $data['code'],
            'status' => (int) $data['status'],
        ]);

        return $this->ok(true);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::query()->find($id);
        if (! $role) {
            return $this->fail('记录不存在', 404, 404);
        }

        if ($role->code === 'super_admin') {
            return $this->fail('系统内置角色不可删除');
        }

        if (User::query()->whereHas('roles', fn ($q) => $q->where('roles.id', $id))->exists()) {
            return $this->fail('角色下仍有用户，无法删除');
        }

        $role->delete();

        return $this->ok(true);
    }

    public function assignMenus(Request $request, int $id): JsonResponse
    {
        $role = Role::query()->find($id);
        if (! $role) {
            return $this->fail('记录不存在', 404, 404);
        }

        $data = $request->validate([
            'menu_ids' => ['required', 'array'],
            'menu_ids.*' => ['integer', 'exists:menus,id'],
        ]);

        DB::transaction(function () use ($role, $data) {
            DB::table('role_menu')->where('role_id', $role->id)->delete();
            $rows = [];
            foreach (array_unique($data['menu_ids']) as $mid) {
                $rows[] = ['role_id' => $role->id, 'menu_id' => $mid];
            }
            if ($rows !== []) {
                DB::table('role_menu')->insert($rows);
            }
        });

        return $this->ok(true);
    }
}
