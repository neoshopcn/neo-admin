# 回收站（独立表 + RecyclesToBin Trait）

回收站将 **业务表整行快照** 存入 `recycle_bin_items`，字段包括：**来源表名**、**JSON 数据**、**进入回收站时间**、**操作人**。业务侧模型在删除前通过 Trait 自动写入快照，然后照常执行 `DELETE`。

后台提供一级菜单 **「回收站管理」**：列表查看、**恢复**（写回业务表并删除回收站记录）、**彻底清除**（仅删除回收站记录）。

---

## 1. 原理与数据流

1. 模型 `use RecyclesToBin` 并注册 `bootRecyclesToBin`（Trait 已写好）。
2. 调用 `$model->delete()` 时触发 `deleting` 事件：
   - `RecycleBinRecorder` 将 `getAttributes()` 写入 `recycle_bin_items`（含 `auth()->id()` 作为操作人；无登录上下文时为 `null`）。
   - 随后 Eloquent 继续执行删除，业务行被删掉。
3. **恢复**：读取快照，按模型策略 `create()` 或 **原始 INSERT** 写回业务表，再删除对应回收站行。
4. **彻底清除**：只删 `recycle_bin_items` 记录（业务数据本来已不存在）。

---

## 2. 数据表结构

迁移：`database/migrations/*_create_recycle_bin_items_table.php`

| 字段 | 说明 |
|------|------|
| `source_table` | 业务表名（如 `posts`） |
| `model_class` | 删除时的模型 FQCN（如 `App\Models\Post`），用于恢复时实例化 |
| `payload` | JSON，键为列名，值为删除瞬间的数据库形态属性 |
| `recycled_at` | 进入回收站时间 |
| `operator_id` | 操作人，关联 `users.id`，可空 |

模型：`App\Models\RecycleBinItem`。

---

## 3. Trait：`App\Models\Concerns\RecyclesToBin`

作用类似「可插拔的删除拦截」——**不是** Eloquent 自带的 `SoftDeletes`（不会在业务表保留 `deleted_at`）。

### 3.1 基础接入（三步）

**①** 执行迁移：

```bash
php artisan migrate
```

**②** 在目标模型上 use Trait：

```php
<?php

namespace App\Models;

use App\Models\Concerns\RecyclesToBin;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use RecyclesToBin;

    protected $table = 'posts';
    // ...
}
```

**③** 正常写删除逻辑即可，无需改 Controller：

```php
$post->delete(); // 先入回收站表，再删 posts 行
```

### 3.2 跳过回收站（不写快照）

适用于运维脚本、数据清洗等场景：

```php
$post->deleteWithoutRecycleBin();
```

### 3.3 关闭回收站（可按环境）

在模型中重写（可选）：

```php
protected static function recycleBinEnabled(): bool
{
    return config('app.env') === 'production'; // 示例：仅生产启用
}
```

---

## 4. 恢复策略与钩子

恢复逻辑在 `App\Services\RecycleBin\RecycleBinRestorer`：

- 仅允许 **`RecyclesToBin` trait 仍挂载在该模型上** 的类恢复（防止随意类名写入）。
- 默认：`Model::unguarded(fn () => Xxx::query()->create($payload))`。

### 4.1 密码等会被「二次加工」的字段（推荐 Raw Insert）

若模型对某列有 mutator / cast（例如 `password` 的 `hashed`），用 `create()` 可能导致哈希被再计算一次。可在模型上重写：

```php
public static function recycleBinUsesRawInsert(): bool
{
    return true;
}
```

恢复时使用 `DB::table($table)->insert($payload)`，要求快照中的类型与数据库一致（JSON 列需已为合法存储格式）。

### 4.2 恢复前清洗快照

```php
/**
 * @param  array<string, mixed>  $payload
 * @return array<string, mixed>
 */
public static function recycleBinHydratePayload(array $payload): array
{
    unset($payload['some_derived_column']);

    return $payload;
}
```

---

## 5. 后台权限与路由（已由项目接好）

| 权限码 | 用途 |
|--------|------|
| `recycle_bin:list` | 菜单 + 列表接口 |
| `recycle_bin:restore` | 恢复 |
| `recycle_bin:purge` | 彻底清除 |

页面：`GET /admin/content/recycle-bin`  

接口：

- `GET /admin/api/recycle-bin/items`（支持 `keyword`、`source_table`、`page`、`page_size`）
- `POST /admin/api/recycle-bin/items/{id}/restore`
- `DELETE /admin/api/recycle-bin/items/{id}`

演示种子：`AdminInitSeeder` 已增加一级菜单「回收站管理」及按钮权限；超级管理员默认拥有全部菜单。

---

## 6. 注意事项

1. **关联与子表**：当前快照仅为 **当前模型这一行**，不会自动备份关联表。若有级联业务，请在文档或 Domain 层自行处理删除顺序，或在恢复后补写关联。
2. **主键冲突**：恢复时若相同主键已存在会失败（接口返回 422）。可先处理冲突或改用 Raw Insert + 调整主键策略。
3. **队列 / 命令行删除**：无 `auth()` 时 `operator_id` 为 `null`，属正常现象。
4. **JSON / 复杂类型列**：依赖数据库驱动与 `getAttributes()` 的形态；若 Raw Insert 报错，检查 JSON 列是否需字符串形态存储。

---

## 7. 完整示例：`Article` 模型（可自行粘贴）

**迁移（业务表）示例：**

```php
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('body')->nullable();
    $table->timestamps();
});
```

**模型：**

```php
<?php

namespace App\Models;

use App\Models\Concerns\RecyclesToBin;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use RecyclesToBin;

    protected $table = 'articles';

    protected $fillable = ['title', 'body'];
}
```

**删除（任意 Controller / Job）：**

```php
Article::query()->findOrFail($id)->delete();
```

之后在 **回收站管理** 中即可对该条快照执行恢复或彻底清除。

---

## 8. 管理员用户模型（可选，慎用）

给 `App\Models\User` 加上 `RecyclesToBin` 后，后台删除用户会先备份再删行。因密码 cast 建议同时：

```php
public static function recycleBinUsesRawInsert(): bool
{
    return true;
}
```

并确认快照列与 `users` 表结构一致。

---

如需扩展「按业务类型自定义摘要展示」，可在 `RecycleBinItemController::transformRow` 中按 `model_class` / `source_table` 分支追加字段（此处未展开，避免与核心 Trait 耦合）。
