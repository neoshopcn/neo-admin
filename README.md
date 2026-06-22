# Neo Admin

## 简介

NeoAdmin 面向需要**可治理、可审计、可扩展**的管理端场景，基于**当代 PHP / Laravel 生态**构建：以 **Laravel 12** 与 **PHP 8.2+** 为后端基座，配合 **Vue 3、Element Plus、Axios** 完成交互层，整体走**轻依赖、易交付**的现代化工程路径，而非堆砌复杂构建链。

在 **AI 辅助研发**上，仓库内置 **`.cursor/rules`** 工程约定（项目架构、后台路由与 CRUD 规范、前后端边界与禁区），使智能体与人类开发者能在**同一套标准**下生成与审阅代码，显著降低「模块风格漂移」与返工成本；业务层保持模块化与清晰扩展面，亦便于按需接入检索增强、运营助手、自动化编排等智能化能力。

## 技术栈

| 层级 | 技术 |
|------|------|
| 后端 | PHP 8.2+，Laravel 12 |
| 前端 | Vue 3，Element Plus，Axios |
| 示例依赖 | Jodit，ECharts |
| 数据库 | MySQL 5.7+（示例配置） |

## 功能概览

- 认证
- 登录日志
- 权限模型
- 用户管理
- 角色与菜单授权
- 谷歌双因子认证(Google 2fa)
- 菜单管理
- 操作日志
- 资源管理
- 文件上传
- 回收站
- 动态配置中心
- 工作台
- 个人中心
- 侧栏导航
- 文档与示例页
- 主题与品牌化

## 环境要求

- PHP **8.2+**
- Composer **2.x**
- **MySQL 5.7+**
- Web 服务器建议 **Nginx**，站点根目录指向 **`public/`**

## 快速开始

在项目根目录执行：

```bash
composer install
copy .env.example .env   # Windows；Linux/macOS：cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

浏览器访问 **`http://127.0.0.1:8000/admin`**（端口以终端为准）。

## 演示账号

| 用户名 | 密码 | 说明 |
|--------|------|------|
| `admin` | `admin123` | 超级管理员，全菜单 |
| `operator` | `operator123` | 受限角色，用于对比权限 |

**生产环境请勿保留默认密码**，部署后请立即修改或重建账号。

## 注意事项

1. **文档根目录**必须为 **`public/`**，勿将仓库根目录直接作为站点根。
2. **勿在 `public/` 下使用名为 `admin` 的目录** 存放静态资源，以免与 `/admin` 路由冲突；静态文件放在 `public/assets/` 下。
3. **会话与缓存**：示例常用 `SESSION_DRIVER=database`、`CACHE_STORE=database`；变更 `.env` 时需同步驱动与表结构。
4. **演示数据**：`db:seed` 适合空库或 `migrate:fresh` 后；在已有数据的生产库重复执行可能冲突。
5. **生产安全**：关闭 `APP_DEBUG`；置于负载均衡后时配置可信代理；定期更新依赖。
