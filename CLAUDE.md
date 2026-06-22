# CLAUDE.md

**Neo Admin** 项目开发指南：架构、目录约定与运行时规范。

---

## 项目简介

**Neo Admin** 是一套面向可治理、可审计、可扩展场景的后台管理系统：

- **后端**：PHP 8.2+，Laravel 12
- **前端**：Vue 3（IIFE）、Element Plus、Axios
- **数据库**：MySQL 5.7+（示例配置）
- **架构**：Laravel + Blade 服务端渲染为主，Vue 做页面交互增强（非 SPA）
- **构建**：不使用 Vite / Webpack / npm 构建链；静态资源本地化

---

## 核心原则

1. **保持 Laravel + Blade 架构** — Vue 仅用于交互增强，不构建 SPA
2. **轻量、低依赖** — 禁止引入 npm 构建、CDN 外链、`.vue` 单文件组件
3. **标准 CRUD + 权限 + 统一响应** — 后台模块遵循固定分层
4. **配置中心统一读取** — 动态配置禁止散落查表或硬编码密钥
5. **最小改动** — 只改任务相关代码，不批量重构、不修改 vendor

---

## 目录结构

```
neo-admin/
├── app/
│   ├── Console/Commands/Admin/     # 运维命令（密码、2FA 等）
│   ├── Http/
│   │   ├── Controllers/Admin/
│   │   │   ├── Api/                # JSON API（CRUD）
│   │   │   ├── Auth/               # 登录、2FA
│   │   │   ├── Content/            # 页面 Controller（__invoke）
│   │   │   └── Concerns/           # ApiResponse、AppliesListFilters
│   │   └── Middleware/             # admin.auth / admin.perm / admin.log
│   ├── Models/                     # Eloquent 模型
│   │   └── Concerns/RecyclesToBin  # 回收站 Trait
│   ├── Services/                   # 业务服务（2FA、回收站、验证码等）
│   ├── Support/                    # ConfigCenter、AdminPermission 等
│   └── helpers.php                 # config_center() 助手函数
├── bootstrap/app.php               # 路由与中间件注册
├── config/
│   ├── admin.php                   # 后台主题、品牌、侧栏等
│   └── upload.php                  # 上传场景配置
├── database/
│   ├── migrations/
│   └── seeders/Admin/              # 菜单、权限、演示数据
├── docs/                           # 回收站、菜单 Seeder 等专题文档
├── public/
│   ├── assets/admin-static/        # Vue、Element Plus、Jodit、ECharts 等
│   └── js/                         # neo-table、neo-upload、neo-richtext 等
├── resources/views/admin/          # Blade 模板
│   ├── layouts/content.blade.php   # 内容页布局（iframe 内页）
│   ├── shell.blade.php             # 后台外壳（侧栏 + iframe）
│   └── content/                    # 各业务页面
├── routes/
│   ├── admin.php                   # ★ 所有后台路由（唯一入口）
│   └── web.php                     # 非 admin 路由（勿写后台业务）
```

---

## 页面架构

```
/admin                    → ShellController（侧栏 + iframe 外壳）
/admin/content/*          → Content Controller → Blade 页面（iframe 内加载）
/admin/api/*              → Api Controller → JsonResponse
```

- **Shell**（`resources/views/admin/shell.blade.php`）：侧栏菜单、面包屑、历史标签、主题切换
- **Content 页**（`resources/views/admin/layouts/content.blade.php`）：独立 Vue 应用挂载点 `#app`，通过 `@stack('scripts')` 加载业务脚本
- **权限码注入**：`window.__ADMIN_PERM_CODES__`（来自 `AdminPermission::sessionCodes()`）
- **Axios 全局配置**：`window.neoAxiosSetup(axios)` — CSRF、401 跳转登录

---

## 后端规范

### 路由（必须遵守）

- **所有后台路由**定义在 `routes/admin.php`
- **禁止**写在 `routes/web.php` 或 `routes/api.php`
- 必须使用 `Route::prefix('admin')->name('admin.')->group(...)`

| 类型 | URL 前缀 | Controller | 返回 |
|------|----------|------------|------|
| 页面 | `/admin/content/*` | `Content\*ManageController` | `view()` |
| API | `/admin/api/*` | `Api\*Controller` | `JsonResponse` |

页面路由使用 `Controller::__invoke()`；API 使用标准 CRUD 方法。

### 权限命名

| 动作 | 权限码格式 | 示例 |
|------|-----------|------|
| 列表 | `xxx:list` | `user:list` |
| 查看 | `xxx:view` | `user:view` |
| 创建 | `xxx:create` | `user:create` |
| 编辑 | `xxx:edit` | `user:edit` |
| 删除 | `xxx:delete` | `user:delete` |

- 页面路由：`->middleware('admin.perm:xxx:list')`
- API 路由：每个操作单独挂载对应权限
- 扩展权限示例：`user:reset_password`、`role:assign_menu`

### Controller 规范

**Content Controller**（页面）：

```php
// app/Http/Controllers/Admin/Content/UserManageController.php
public function __invoke(): View
{
    return view('admin.content.users', [
        'neo' => [
            'title' => '管理员用户',
            'listUrl' => url('/admin/api/users'),
            // columns, filters, formFields, perms ...
        ],
    ]);
}
```

**API Controller**：

```php
namespace App\Http\Controllers\Admin\Api;

class UserController extends Controller
{
    use ApiResponse;
    use AppliesListFilters;
    // index / show / store / update / destroy
}
```

### 统一 API 响应

使用 `App\Http\Controllers\Admin\Concerns\ApiResponse`：

```php
return $this->ok($data);                          // code: 0
return $this->fail('记录不存在', 404, 404);       // code: 404
return $this->paginate($query->paginate($pageSize)); // list + pagination
```

响应结构：

```json
{
  "code": 0,
  "message": "success",
  "data": { "list": [], "pagination": { "page": 1, "page_size": 15, "total": 0 } }
}
```

### 列表查询

必须支持：

- `keyword` — 模糊搜索（`applyKeyword`）
- 精确筛选如 `status`（`applyExact`）
- 时间范围 `created_from` / `created_to`（`applyDateRange`）

分页参数：

- `page`、`page_size`（默认 15，最大 100）

### 数据校验与安全

- 所有写操作使用 `$request->validate()`
- 唯一性：`Rule::unique(...)`
- 数组字段：`role_ids.*` + `exists:roles,id`
- 所有 ID 路由使用 `->whereNumber('id')`
- 禁止删除当前登录用户
- 不存在的数据返回 `$this->fail(...)`
- 不信任前端输入；数组去重、过滤；显式类型转换

### 中间件

| 别名 | 类 | 用途 |
|------|-----|------|
| `admin.auth` | `AdminAuthenticate` | 后台登录态 |
| `admin.perm` | `AdminPermission` | 权限校验 |
| `admin.log` | `LogAdminOperation` | 操作日志（API 组默认启用） |

---

## 前端规范

### 必须使用

- Vue 3 **IIFE** 版本（`vue.global.js`）
- Element Plus **本地静态资源**（`public/assets/admin-static/element-plus/`）
- Axios 请求接口
- 中文 locale：`ElementPlusLocaleZhCn`

### 禁止

- `.vue` 文件
- Vite / Webpack / npm
- CDN imports

### 页面脚本模式

```blade
@extends('admin.layouts.content')
@section('title', '页面标题')
@push('scripts')
<script>window.__NEO_CONFIG__ = @json($neo);</script>
<script src="{{ asset('js/neo-table.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    neoAxiosSetup(axios);
    NeoTable.mount('#app', window.__NEO_CONFIG__);
});
</script>
@endpush
```

### 公共 JS 组件

| 文件 | 用途 |
|------|------|
| `public/js/neo-table.js` | 通用 CRUD 表格（筛选、分页、表单弹窗） |
| `public/js/neo-upload.js` | 文件/图片上传（对接资源库） |
| `public/js/neo-richtext.js` | 富文本（Jodit） |
| `public/js/config-center.js` | 配置中心表单 |
| `public/js/menus-admin.js` | 菜单树管理 |

### 静态资源约定

- 第三方库放在 `public/assets/admin-static/` 下
- **禁止**在 `public/` 下创建名为 `admin` 的目录（与 `/admin` 路由冲突）
- 业务脚本放在 `public/js/`

---

## 动态配置中心

业务读取配置中心数据时，**必须**使用统一入口，禁止直接查 `config_items` 表。

| 入口 | 位置 |
|------|------|
| `ConfigCenter` 类 | `App\Support\ConfigCenter` |
| 助手函数 | `config_center('api.wechat.pay.mch_id', '')` |

### 路径约定

```
page.group.section.item
```

- **page**：`system`（系统配置）| `api`（接口配置）
- **group**：如 `site` / `login` / `wechat` / `email`
- **section**：如 `default` / `mini` / `pay`
- **item**：如 `site_name` / `mch_id`

### 读取示例

```php
config_center('system.login.default.login_captcha', false);
ConfigCenter::item('api', 'wechat', 'pay', 'mch_id', '');
ConfigCenter::section('api', 'wechat', 'pay');
```

### 返回值类型

| type | 返回 |
|------|------|
| switch | bool |
| number | int / float |
| json | array |
| 其他 | string |
| 空值 | null（可传默认值） |

### 缓存

- 缓存键：`config_center:index`
- 后台保存或 Seeder 后调用 `ConfigCenter::forgetCache()`
- 直接改库需手动清缓存

### 禁止

- 业务代码中 `ConfigItem::query()->where('name', ...)`
- 用 Laravel `config()` 读动态配置中心数据
- 硬编码应从配置中心获取的密钥、开关、站点信息

新增配置项时，先查 `database/seeders/Admin/ConfigCenterDataSeeder.php` 确认 path。

---

## 微信 SDK（概要）

- **依赖**：`w7corp/easywechat:^6.17`；业务层**禁止**直接使用 EasyWeChat SDK
- **入口**：`WechatManager::miniProgram()` / `payment()` / `officialAccount()`；配置变更后 `forgetInstances()`
- **配置**：仅 `config_center('api.wechat.mini|mp|pay', [])`；映射集中在 `ConfigMapper`；禁止 `config('wechat')`
- **证书**：配置中心存 PEM 全文（含 BEGIN/END），直传 SDK，不落盘
- **封装**（`App\Support\Wechat\*`）：
  - `MiniProgram` — `code2Session`、`getUnlimitedQRCode`、`decryptSession`
  - `Payment` — `buildMiniProgramPayParams`（下单+调起参数）、`createDomesticRefund`、`handlePayNotify`
  - `OfficialAccount` — `getServer` / `getOAuth` / `getClient`
- **日志**：`Log::channel('wechat')` → `storage/logs/wechat.log`（single）；写在封装层；context 含 `action`；禁止记 secret/PEM/session_key
- **支付安全**：回调须查单确认终态；回调路由 POST 且排除 CSRF
- **分层**：微信 API 在 Support 封装；订单/退款业务在 Service；Controller 只做校验与响应

---

## 权限与菜单

### 运行时鉴权

- **后端**：`admin.perm:权限码` 中间件 + `App\Support\AdminPermission`
- **前端**：`window.__ADMIN_PERM_CODES__`；页面内 `hasPerm('user:create')` 控制按钮
- **侧栏**：角色关联菜单树，由 `/admin/api/sidebar/menus` 下发

超管角色合并权限表与菜单上的全部 `permission_code`。

### 新增模块 Seeder 流程

详见 `docs/admin-menu-permission-seed.md`。标准步骤：

1. 在 `routes/admin.php` 注册页面 + API 路由及 `admin.perm`
2. 新建 `database/seeders/Admin/XxxMenuSeeder.php`：
   - `PERMISSIONS` 常量 → `AdminSeedSupport::syncPermissions()`
   - `syncMenuByPermissionCode()` 创建菜单与按钮
3. （可选）注册到 `AdminInitSeeder::call([...])`
4. 给演示角色开放：修改 `AdminRolesAndUsersSeeder` 或在后台「角色授权」勾选

```bash
php artisan db:seed --class="Database\Seeders\Admin\OrderManagementMenuSeeder"
```

新增权限后，用户需**重新登录**以刷新会话中的权限码。

---

## 回收站

详见 `docs/recycle-bin.md`。

模型接入：

```php
use App\Models\Concerns\RecyclesToBin;

class Post extends Model
{
    use RecyclesToBin;
}
```

- `$model->delete()` → 先写入 `recycle_bin_items`，再删业务行
- `$model->deleteWithoutRecycleBin()` → 跳过回收站
- 密码等 mutator 字段恢复：重写 `recycleBinUsesRawInsert(): bool`

权限码：`recycle_bin:list` / `restore` / `purge`

---

## 新增后台模块清单

生成新模块时，按顺序完成：

1. **Migration** — 业务表（如需要）
2. **Model** — Eloquent 模型（可选 `RecyclesToBin`）
3. **routes/admin.php** — content 页面路由 + api CRUD 路由 + 权限中间件
4. **Content Controller** — `__invoke()` 返回 Blade + `$neo` 配置
5. **Api Controller** — `ApiResponse` + `AppliesListFilters` + validate
6. **Blade 视图** — 继承 `admin.layouts.content`，挂载 `neo-table` 或自定义 Vue
7. **MenuSeeder** — 权限与菜单（参考 `UserManagementMenuSeeder`）
8. **（可选）** 注册到 `AdminInitSeeder`

`$neo` 配置关键字段：

| 字段 | 说明 |
|------|------|
| `title` | 页面标题 |
| `listUrl` | 列表 API |
| `rowKey` | 行主键，通常 `id` |
| `columns` | 表格列定义 |
| `filters` | 筛选器（input / select / daterange） |
| `formFields` | 表单字段（含 upload、select 等） |
| `perms` | 前端按钮权限映射 |

参考实现：`UserManageController` + `users.blade.php` + `Api\UserController`

---

## 保护与禁区

### 禁止修改

- `vendor/**`
- `bootstrap/cache/**`

### 仅在明确要求时允许

- `tests/**`

### 禁止主动执行

- 删除目录
- 批量重构项目结构
- 修改第三方库源码

---

## 常用命令

```bash
# 安装与初始化
composer install
copy .env.example .env          # Windows；Linux/macOS: cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed

# 开发服务
php artisan serve

# 演示数据（事务内幂等）
php artisan db:seed --class=AdminInitSeeder

# 单独模块 Seeder
php artisan db:seed --class="Database\Seeders\Admin\UserManagementMenuSeeder"

# 代码风格（可选）
./vendor/bin/pint
```

---

## 部署注意事项

1. Web 根目录必须为 **`public/`**
2. 勿在 `public/` 下使用 `admin` 目录名
3. 生产环境关闭 `APP_DEBUG`，修改默认演示密码
4. 示例常用 `SESSION_DRIVER=database`、`CACHE_STORE=database`
5. `db:seed` 适合空库；生产库重复执行可能覆盖 Seeder 匹配键的记录

---

## 配置文件速查

| 文件 | 用途 |
|------|------|
| `config/admin.php` | 后台标题、品牌、主题、侧栏宽度、Logo |
| `config/upload.php` | 上传场景（avatar、document 等） |
| `config/filesystems.php` | 存储磁盘与 public URL |
| `config/captcha.php` | 登录验证码 |

后台静态配置（非配置中心）用 Laravel `config('admin.xxx')`；业务动态配置用 `config_center()`。

---

## 相关文档

| 文档 | 内容 |
|------|------|
| `README.md` | 项目介绍、快速开始 |
| `docs/admin-menu-permission-seed.md` | 菜单、权限、Seeder 详解 |
| `docs/recycle-bin.md` | 回收站 Trait 接入 |
