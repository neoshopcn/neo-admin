<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

/**
 * 按角色菜单汇总 permission_code
 */
class AdminPermission
{
    public static function codesForUser(User $user): array
    {
        $roles = $user->relationLoaded('roles')
            ? $user->roles
            : $user->roles()->get();

        if ($roles->contains(fn (Role $r) => $r->isSuper())) {
            $fromPerms = Permission::query()->orderBy('id')->pluck('code');
            $fromMenus = Menu::query()->whereNotNull('permission_code')->pluck('permission_code');

            return $fromPerms->merge($fromMenus)->unique()->values()->all();
        }

        $roleIds = $roles->pluck('id')->all();
        if ($roleIds === []) {
            return [];
        }

        return Menu::query()
            ->join('role_menu', 'menus.id', '=', 'role_menu.menu_id')
            ->whereIn('role_menu.role_id', $roleIds)
            ->whereNotNull('menus.permission_code')
            ->pluck('menus.permission_code')
            ->unique()
            ->values()
            ->all();
    }

    public static function refreshSession(User $user): void
    {
        session(['admin_perm_codes' => self::codesForUser($user)]);
    }

    public static function sessionCodes(): array
    {
        return session('admin_perm_codes', []);
    }

    public static function userHasCode(?User $user, string $code): bool
    {
        if (! $user) {
            return false;
        }

        $roles = $user->relationLoaded('roles')
            ? $user->roles
            : $user->roles()->get(['id', 'code', 'name']);

        if ($roles->contains(fn (Role $r) => $r->isSuper())) {
            return true;
        }

        return in_array($code, self::sessionCodes(), true);
    }
}
