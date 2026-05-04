<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\AdminLoginLog;
use App\Models\RecycleBinItem;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** 工作台 */
class DashboardController extends Controller
{
    /** @var list<string> */
    private const REQUIRED_EXTENSIONS = [
        'openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl',
    ];

    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $fullAccess = $user->hasSuperRole();

        $resourceBase = Resource::query();
        $recycleBase = RecycleBinItem::query();
        if (! $fullAccess) {
            $resourceBase->where('user_id', $user->id);
            $recycleBase->where('operator_id', $user->id);
        }

        $totalBytes = (int) (clone $resourceBase)->sum('size_bytes');

        $loginQuery = AdminLoginLog::query()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(15);

        if (! $fullAccess) {
            $loginQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('username', $user->username);
            });
        }

        $loginLogs = $loginQuery
            ->get()
            ->map(fn (AdminLoginLog $log) => [
                'id' => $log->id,
                'username' => $log->username,
                'name' => $log->user?->name,
                'ip' => $log->ip ?? '—',
                'success' => $log->success,
                'created_at' => $log->created_at?->format('Y-m-d H:i:s') ?? '—',
                'user_agent_short' => self::shortUa($log->user_agent),
            ])
            ->values()
            ->all();

        return view('admin.content.dashboard', [
            'dashboardPage' => [
                'welcomeName' => $user->name ?: $user->username,
                'statsScoped' => ! $fullAccess,
                'onboardingSteps' => self::onboardingSteps(),
                'resourceStats' => [
                    'total_count' => (clone $resourceBase)->count(),
                    'total_bytes' => $totalBytes,
                    'total_bytes_label' => self::formatBytes($totalBytes),
                    'active_count' => (clone $resourceBase)->where('status', 1)->count(),
                    'disabled_count' => (clone $resourceBase)->where('status', 0)->count(),
                ],
                'recycleBinStats' => [
                    'total_count' => (clone $recycleBase)->count(),
                ],
                'clientIp' => $request->ip(),
                'serverInfo' => $this->serverInfoRows(),
                'extensionChecks' => $this->extensionChecks(),
                'loginLogsFullAccess' => $fullAccess,
                'loginLogs' => $loginLogs,
                'copyrightFooter' => config('admin.copyright_footer_enabled', true)
                    ? (config('admin.copyright_footer_name', 'NeoAdmin').' © 2025–'.date('Y'))
                    : null,
            ],
        ]);
    }

    /**
     * @return list<array{title: string, description: string}>
     */
    private static function onboardingSteps(): array
    {
        return [
            [
                'title' => '认识菜单与权限',
                'description' => '左侧导航随角色权限变化；无菜单则无权限。头像菜单可进入个人资料与安全设置。',
            ],
            [
                'title' => '使用资源中心',
                'description' => '在「资源管理」中上传、检索与管理文件；上传记录与账号关联。',
            ],
            [
                'title' => '回收站与恢复',
                'description' => '删除写入回收站快照后，可在「回收站」恢复或彻底清除；非管理员一般仅能查看本人删除的记录。',
            ],
            [
                'title' => '安全与登录痕迹',
                'description' => '建议定期修改密码；若系统启用两步验证请及时配置。您可在下方「登录日志」中查看与本账号相关的登录尝试记录。',
            ],
        ];
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function serverInfoRows(): array
    {
        $mysql = '—';
        try {
            $v = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $mysql = is_string($v) ? $v : '—';
        } catch (\Throwable) {
            // ignore
        }

        $web = $_SERVER['SERVER_SOFTWARE'] ?? php_sapi_name();

        return [
            ['label' => '操作系统', 'value' => php_uname('s').' '.php_uname('r').' ('.PHP_OS_FAMILY.')'],
            ['label' => 'PHP 版本', 'value' => PHP_VERSION],
            ['label' => 'MySQL 版本', 'value' => $mysql],
            ['label' => 'Web Server', 'value' => is_scalar($web) ? (string) $web : '—'],
            ['label' => '服务器时区', 'value' => config('app.timezone')],
        ];
    }

    /**
     * @return list<array{name: string, loaded: bool}>
     */
    private function extensionChecks(): array
    {
        $rows = [];
        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $rows[] = ['name' => $ext, 'loaded' => extension_loaded($ext)];
        }

        return $rows;
    }

    private static function formatBytes(int $bytes): string
    {
        $b = max(0, $bytes);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        if ($b < 1) {
            return '0 B';
        }
        $pow = (int) min(floor(log($b, 1024)), count($units) - 1);
        $b /= 1024 ** $pow;
        $dec = $pow >= 2 ? 2 : 0;

        return round($b, $dec).' '.$units[$pow];
    }

    private static function shortUa(?string $ua): string
    {
        if ($ua === null || $ua === '') {
            return '—';
        }
        $ua = trim($ua);
        if (strlen($ua) <= 96) {
            return $ua;
        }

        return substr($ua, 0, 93).'…';
    }
}
