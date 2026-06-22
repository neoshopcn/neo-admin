# 菜单、权限与角色菜单（Seeder 使用说明）

本文说明 `database/seeders` 下后台演示数据的组织方式：如何声明**权限**、**菜单**、以及**角色与菜单**的绑定，并给出可复制的示例。

---

## 1. 概念关系

| 概念 | 表 / 关系 | 说明 |
|------|-----------|------|
| **权限** | `permissions` | `code` 唯一，与路由中间件 `admin.perm:xxx`、菜单上的 `permission_code` 对齐。 |
| **菜单** | `menus` | `type`：`0` 目录、`1` 页面菜单、`2` 按钮；可挂 `permission_code` + `permission_id`。 |
| **角色 ↔ 菜单** | `role_menu` | 用户能看哪些菜单、侧栏展示哪些节点，由角色关联的**菜单 id** 决定；非超管还会据此汇总 `permission_code` 供前端 `hasPerm` 使用。 |

演示账号与角色在 `AdminRolesAndUsersSeeder` 中维护：**超级管理员**同步**全部**菜单 id；**演示运营**只同步一组 `permission_code` 对应的菜单及其**祖先目录**（见 `expandMenuAncestors`）。

---

## 2. 目录与入口

```
database/seeders/
├── DatabaseSeeder.php          # 入口，通常 call AdminInitSeeder
├── AdminInitSeeder.php         # 事务内编排各模块 Seeder
└── Admin/
    ├── AdminSeedSupport.php    # 幂等辅助（权限 / 菜单）
    ├── AdminSystemDirectorySeeder.php
    ├── DashboardMenuSeeder.php
    ├── ResourceMenuSeeder.php
    ├── RecycleBinMenuSeeder.php
    ├── DocumentationMenuSeeder.php
    ├── UserManagementMenuSeeder.php
    ├── RoleManagementMenuSeeder.php
    ├── MenuManagementMenuSeeder.php
    ├── OperationLogMenuSeeder.php
    └── AdminRolesAndUsersSeeder.php   # 角色、用户、role_menu
```

**执行示例：**

```bash
php artisan db:seed
# 或仅演示数据
php artisan db:seed --class=AdminInitSeeder
```

`AdminInitSeeder` 在**单事务**中执行，失败会整体回滚；每次开始会 `AdminSeedSupport::clearPermissionCache()`，避免跨次运行的静态缓存干扰。

### 2.1 单独执行某个 Seeder（不必写入 AdminInitSeeder）

模块 Seeder **不是**必须加入 `AdminInitSeeder.php`。只要类文件存在且命名空间正确，可用命令行**只跑这一类**：

```bash
php artisan db:seed --class="Database\Seeders\Admin\OrderManagementMenuSeeder"
```

`--class` 为完整类名（含命名空间），与 `namespace`、文件路径一致即可。

| 方式 | 适用场景 |
|------|----------|
| 写入 `AdminInitSeeder::call([...])` | `php artisan db:seed` / `db:seed --class=AdminInitSeeder` 时**一键**写入全部演示菜单与权限，适合新环境、CI 全量。 |
| `db:seed --class=某个 Seeder` | **只补**某一模块的权限/菜单，不必改编排文件；可多次执行（幂等）。 |

两种方式可**同时采用**：日常全量用 `AdminInitSeeder`，线上或本地只增量时用 `--class`。

**依赖前提：** 若该 Seeder 调用了 `systemManagementDirectoryId()`，库里须已有「系统管理」目录（通常由 `AdminSystemDirectorySeeder` 写入）。仅在新库上跑模块 Seeder 时，可先执行：

```bash
php artisan db:seed --class="Database\Seeders\Admin\AdminSystemDirectorySeeder"
php artisan db:seed --class="Database\Seeders\Admin\OrderManagementMenuSeeder"
```

单独执行模块 Seeder **不会**自动更新 `AdminRolesAndUsersSeeder` 里的 `role_menu`；若要给某角色开放新菜单，仍需在后台「角色授权」里勾选，或改 `AdminRolesAndUsersSeeder` 后再跑一次该 Seeder。

---

## 3. AdminSeedSupport 常用方法

### 3.1 `syncPermissions(array $codeToName)`

按 `permissions.code` **幂等**写入/更新展示名。

```php
AdminSeedSupport::syncPermissions([
    'order:list' => '订单列表',
    'order:export' => '订单导出',
]);
```

### 3.2 `syncMenuFolder(int $parentId, string $name, array $fields)`

**无权限码**的目录。匹配键：`(parent_id, name, type=0)`。

```php
AdminSeedSupport::syncMenuFolder(0, '营销管理', [
    'icon' => 'Promotion',
    'sort' => 25,
    'status' => 1,
]);
```

### 3.3 `syncMenuByPermissionCode(string $permissionCode, array $fields)`

**页面菜单或按钮**（必须已在 `permissions` 中存在对应 `code`）。匹配键：`permission_code`。

`$fields` 需包含：`parent_id`、`name`、`type`（`1` 菜单 / `2` 按钮）、`icon`、`path`、`sort`、`status` 等。

### 3.4 `systemManagementDirectoryId()`

返回根目录「系统管理」的 `menus.id`。依赖已执行 `AdminSystemDirectorySeeder`。

### 3.5 `expandMenuAncestors(array $menuIds)`

根据菜单 id 集合，向上补齐所有父级 id，用于「只授权叶子菜单时，侧栏仍能显示目录」。

---

## 4. 新增业务模块：标准步骤

1. **路由**（`routes/admin.php`）为页面 / API 配置 `admin.perm:模块:动作`。
2. 在 `database/seeders/Admin/` 新建 `XxxMenuSeeder.php`：
   - 顶部 `private const PERMISSIONS = [...]`；
   - `run()` 中先 `syncPermissions`，再 `syncMenuFolder` / `syncMenuByPermissionCode`。
3. **（可选）**将 `XxxMenuSeeder::class` 插入 `AdminInitSeeder::call([...])` 的**合适顺序**，以便全量 seed 时带上该模块；若只想命令行增量执行，可跳过本步，改用 `php artisan db:seed --class=Database\Seeders\Admin\XxxMenuSeeder`（见 §2.1）。依赖「系统管理」的模块，须保证已跑过 `AdminSystemDirectorySeeder`。
4. 若需给**演示运营**等新角色开放菜单：在 `AdminRolesAndUsersSeeder` 里调整 `$opCodes` 或单独 `sync` 逻辑；仅单独跑模块 Seeder 时，不会自动改角色菜单，需在后台授权或再跑角色 Seeder。
5. 生产环境首次上线：确保执行过 Seeder 或等价 SQL，否则 `permissions` / `menus` / `role_menu` 不完整。

---

## 5. 完整示例：新增「订单管理」模块 Seeder

假设路由已使用 `order:list`、`order:view` 等权限码。

**文件：** `database/seeders/Admin/OrderManagementMenuSeeder.php`

```php
<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;

class OrderManagementMenuSeeder extends Seeder
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'order:list' => '订单列表',
        'order:view' => '订单查看',
        'order:export' => '订单导出',
    ];

    public function run(): void
    {
        AdminSeedSupport::syncPermissions(self::PERMISSIONS);

        $systemId = AdminSeedSupport::systemManagementDirectoryId();

        $parent = AdminSeedSupport::syncMenuByPermissionCode('order:list', [
            'parent_id' => $systemId,
            'name' => '订单管理',
            'icon' => 'ShoppingCart',
            'path' => '/admin/content/orders',
            'sort' => 45,
            'status' => 1,
            'type' => 1,
        ]);

        foreach ([
            ['order:view', '订单-查看'],
            ['order:export', '订单-导出'],
        ] as [$code, $label]) {
            AdminSeedSupport::syncMenuByPermissionCode($code, [
                'parent_id' => $parent->id,
                'name' => $label,
                'icon' => null,
                'path' => null,
                'sort' => 10,
                'status' => 1,
                'type' => 2,
            ]);
        }
    }
}
```

**（可选）在 `AdminInitSeeder.php` 中注册**，以便全量 `db:seed` 时一并执行：

```php
$this->call([
    AdminSystemDirectorySeeder::class,
    // ... 其他模块 ...
    OperationLogMenuSeeder::class,
    OrderManagementMenuSeeder::class,   // 新增
    AdminRolesAndUsersSeeder::class,
]);
```

若不注册，也可在已有「系统管理」目录的前提下单独执行：

```bash
php artisan db:seed --class="Database\Seeders\Admin\OrderManagementMenuSeeder"
```

**给演示运营开放「仅列表 + 查看」时**，在 `AdminRolesAndUsersSeeder` 的 `$opCodes` 中追加：

```php
'order:list',
'order:view',
```

保存后重新执行 `AdminInitSeeder`，或仅同步角色菜单：

```bash
php artisan db:seed --class="Database\Seeders\Admin\AdminRolesAndUsersSeeder"
```

`expandMenuAncestors` 会自动带上「系统管理」等父级目录。

---

## 6. 幂等与数据安全

| 行为 | 策略 |
|------|------|
| 权限 | `Permission::updateOrCreate(['code' => …], …)` |
| 目录菜单 | `Menu::updateOrCreate(['parent_id','name','type'=>0], …)` |
| 带 `permission_code` 的菜单/按钮 | `Menu::updateOrCreate(['permission_code' => …], …)` |
| 角色 | `Role::updateOrCreate(['code' => …], …)` |
| 演示用户 | `User::firstOrCreate(['username' => …], …)`，**不反复覆盖已存在用户的密码** |
| 角色菜单 | `$role->menus()->sync($ids)`，与当前菜单树对齐 |

可多次执行 `AdminInitSeeder`；若业务库已手工改过菜单名/排序，下次 seed 会以 `updateOrCreate` 的匹配键为准覆盖同键记录，生产环境请谨慎执行。

---

## 7. 与运行时的关系

- **后端鉴权**：`admin.perm:权限码` 与 `App\Support\AdminPermission`。
- **前端按钮**：`window.__ADMIN_PERM_CODES__`（来自会话中的菜单权限汇总；超管会合并权限表与菜单上的 code，见 `AdminPermission::codesForUser`）。
- **侧栏**：由角色关联的菜单树接口下发；无 `role_menu` 则无入口。

新增权限后，相关用户需**重新登录**（或触发 `AdminPermission::refreshSession`）再刷新前端权限缓存。

---

## 8. 模块 Seeder 一览（当前仓库）

| Seeder | 内容概要 |
|--------|----------|
| `AdminSystemDirectorySeeder` | 根目录「系统管理」 |
| `DashboardMenuSeeder` | 工作台 |
| `ResourceMenuSeeder` | 资源管理 + 按钮 |
| `RecycleBinMenuSeeder` | 回收站 |
| `DocumentationMenuSeeder` | 文档示例目录及子页 |
| `UserManagementMenuSeeder` | 用户管理 |
| `RoleManagementMenuSeeder` | 角色管理 |
| `MenuManagementMenuSeeder` | 菜单管理 |
| `OperationLogMenuSeeder` | 操作日志 + 删除 |
| `AdminRolesAndUsersSeeder` | 角色、用户、`role_menu` |

更细的字段含义以迁移文件 `database/migrations/*_create_menus_table.php`、`*_create_permissions_table.php` 为准。
