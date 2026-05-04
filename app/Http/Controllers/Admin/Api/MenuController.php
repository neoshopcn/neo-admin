<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MenuController extends Controller
{
    use ApiResponse;

    /** 完整菜单树（含按钮） */
    public function tree(): JsonResponse
    {
        $menus = Menu::query()->orderBy('sort')->get();

        return $this->ok($this->buildTree($menus));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $menu = Menu::query()->create($data);

        return $this->ok(['id' => $menu->id]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $menu = Menu::query()->find($id);
        if (! $menu) {
            return $this->fail('记录不存在', 404, 404);
        }

        $menu->update($this->validated($request));

        return $this->ok(true);
    }

    public function destroy(int $id): JsonResponse
    {
        $menu = Menu::query()->find($id);
        if (! $menu) {
            return $this->fail('记录不存在', 404, 404);
        }

        if (Menu::query()->where('parent_id', $menu->id)->exists()) {
            return $this->fail('请先删除子菜单');
        }

        $menu->delete();

        return $this->ok(true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'parent_id' => ['required', 'integer', 'min:0'],
            'name' => ['required', 'string', 'max:128'],
            'icon' => ['nullable', 'string', 'max:64'],
            'path' => ['nullable', 'string', 'max:255'],
            'sort' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'integer', 'in:0,1'],
            'type' => ['required', 'integer', 'in:0,1,2'],
            'permission_code' => ['nullable', 'string', 'max:128'],
            'permission_id' => ['nullable', 'integer', 'exists:permissions,id'],
        ]);
    }

    /**
     * @param  Collection<int, Menu>  $flat
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
                'sort' => $menu->sort,
                'status' => $menu->status,
                'type' => $menu->type,
                'permission_code' => $menu->permission_code,
                'permission_id' => $menu->permission_id,
                'children' => $this->buildTree($flat, (int) $menu->id),
            ];
        }

        return $branch;
    }
}
