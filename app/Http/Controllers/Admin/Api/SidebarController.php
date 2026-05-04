<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Support\AdminPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SidebarController extends Controller
{
    use ApiResponse;

    /** 当前用户可见侧栏菜单树 */
    public function menus(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('roles');

        $query = Menu::query()->where('status', 1)->whereIn('type', [0, 1])->orderBy('sort');

        if ($user->hasSuperRole()) {
            $menus = $query->get();
        } else {
            $roleIds = $user->roles->pluck('id')->all();
            $ids = $roleIds !== []
                ? DB::table('role_menu')->whereIn('role_id', $roleIds)->distinct()->pluck('menu_id')->all()
                : [];

            if ($ids === []) {
                return $this->ok([]);
            }

            $expanded = $this->expandWithAncestors(array_map('intval', $ids));
            $menus = $query->whereIn('id', $expanded)->get();
        }

        $tree = $this->buildTree($menus);

        return $this->ok($tree);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles');

        return $this->ok([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'role_name' => $user->roles->pluck('name')->filter()->implode('、') ?: null,
            'perm_codes' => AdminPermission::sessionCodes(),
        ]);
    }

    /** 更新当前用户资料 */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'email' => ['nullable', 'string', 'email', 'max:190'],
                'avatar' => ['nullable', 'string', 'max:512'],
            ], [
                'name.required' => '请填写姓名',
                'email.email' => '邮箱格式不正确',
            ]);
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first(), 422, 422);
        }

        $user->name = $data['name'];
        $user->email = $data['email'] ?? null;
        $user->avatar = $data['avatar'] ?? null;
        $user->save();

        $user->loadMissing('roles');

        return $this->ok([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'role_name' => $user->roles->pluck('name')->filter()->implode('、') ?: null,
        ]);
    }

    /**
     * @param  Collection<int, Menu>  $flat
     * @return array<int, mixed>
     */
    protected function buildTree($flat, int $parentId = 0): array
    {
        $branch = [];
        foreach ($flat as $menu) {
            if ((int) $menu->parent_id !== $parentId) {
                continue;
            }
            $branch[] = [
                'id' => $menu->id,
                'parent_id' => $menu->parent_id,
                'name' => $menu->name,
                'icon' => $menu->icon,
                'path' => $menu->path,
                'type' => $menu->type,
                'children' => $this->buildTree($flat, (int) $menu->id),
            ];
        }

        return $branch;
    }

    /**
     * 补齐已授权菜单的祖先节点
     *
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    protected function expandWithAncestors(array $ids): array
    {
        $set = array_fill_keys($ids, true);
        $queue = $ids;
        $rows = Menu::query()->whereIn('id', $ids)->get(['id', 'parent_id'])->keyBy('id');

        while ($queue !== []) {
            $id = array_pop($queue);
            $row = $rows->get($id);
            if (! $row) {
                $row = Menu::query()->find($id, ['id', 'parent_id']);
                if ($row) {
                    $rows->put($id, $row);
                }
            }
            $pid = (int) ($row->parent_id ?? 0);
            if ($pid > 0 && ! isset($set[$pid])) {
                $set[$pid] = true;
                $queue[] = $pid;
            }
        }

        return array_map('intval', array_keys($set));
    }
}
