<?php

namespace Database\Seeders\Admin;

use App\Models\Menu;
use App\Models\Permission;

/**
 * 后台演示菜单/权限：声明式 seed 辅助，幂等 updateOrCreate。
 */
final class AdminSeedSupport
{
    /** @var array<string, int> */
    private static array $permissionIdCache = [];

    public static function clearPermissionCache(): void
    {
        self::$permissionIdCache = [];
    }

    /**
     * @param  array<string, string>  $codeToName  permission_code => 展示名
     */
    public static function syncPermissions(array $codeToName): void
    {
        foreach ($codeToName as $code => $name) {
            Permission::query()->updateOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
            self::$permissionIdCache[$code] = (int) Permission::query()->where('code', $code)->value('id');
        }
    }

    public static function permissionId(string $code): int
    {
        if (! isset(self::$permissionIdCache[$code])) {
            $id = Permission::query()->where('code', $code)->value('id');
            if ($id === null) {
                throw new \RuntimeException("Permission not found after sync: {$code}");
            }
            self::$permissionIdCache[$code] = (int) $id;
        }

        return self::$permissionIdCache[$code];
    }

    /**
     * 目录（无 permission）
     */
    public static function syncMenuFolder(int $parentId, string $name, array $fields): Menu
    {
        /** @var Menu $menu */
        $menu = Menu::query()->updateOrCreate(
            [
                'parent_id' => $parentId,
                'name' => $name,
                'type' => 0,
            ],
            array_merge([
                'icon' => null,
                'path' => null,
                'sort' => 0,
                'status' => 1,
                'permission_code' => null,
                'permission_id' => null,
            ], $fields)
        );

        return $menu;
    }

    /**
     * 菜单或按钮（有 permission_code，与 permissions 表对应）
     *
     * @param  array<string, mixed>  $fields  需含 parent_id, name, type, icon, path, sort, status
     */
    public static function syncMenuByPermissionCode(string $permissionCode, array $fields): Menu
    {
        $permissionId = self::permissionId($permissionCode);

        /** @var Menu $menu */
        $menu = Menu::query()->updateOrCreate(
            ['permission_code' => $permissionCode],
            array_merge($fields, [
                'permission_code' => $permissionCode,
                'permission_id' => $permissionId,
            ])
        );

        return $menu;
    }

    public static function systemManagementDirectoryId(): int
    {
        $id = Menu::query()
            ->where('parent_id', 0)
            ->where('name', '系统管理')
            ->where('type', 0)
            ->value('id');

        if ($id === null) {
            throw new \RuntimeException('请先执行 AdminSystemDirectorySeeder');
        }

        return (int) $id;
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    public static function expandMenuAncestors(array $ids): array
    {
        $set = collect($ids)->unique()->values();
        $guard = 0;
        while ($guard++ < 20) {
            $parents = Menu::query()->whereIn('id', $set)->pluck('parent_id')->filter(fn ($p) => (int) $p > 0)->unique();
            $before = $set->count();
            $set = $set->merge($parents)->unique()->values();
            if ($set->count() === $before) {
                break;
            }
        }

        return $set->map(fn ($v) => (int) $v)->all();
    }
}
