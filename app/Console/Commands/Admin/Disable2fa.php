<?php

namespace App\Console\Commands\Admin;

use App\Models\User;
use App\Services\Google2faService;
use Illuminate\Console\Command;

class Disable2fa extends Command
{
    protected $signature = 'admin:disable-2fa {username : 后台登录账号}';

    protected $description = '关闭指定账号的双因子验证，并清空密钥、锁定时间与防重放记录';

    public function handle(Google2faService $google2fa): int
    {
        $username = trim((string) $this->argument('username'));
        if ($username === '') {
            $this->components->error('账号不能为空。');

            return self::FAILURE;
        }

        $user = User::query()->where('username', $username)->first();
        if ($user === null) {
            $this->components->error("用户 [{$username}] 不存在");

            return self::FAILURE;
        }

        $wasEnabled = $user->isGoogle2faEnabled();
        $hadSecret = $user->google2fa_secret !== null && $user->google2fa_secret !== '';
        $wasLocked = $user->isGoogle2faLocked();

        $google2fa->disable($user);

        $this->components->info("已关闭用户 [{$username}] 的双因子验证");
        $this->line('  · 双因子状态：'.($wasEnabled ? '已开启 → 已关闭' : '原本未开启（已确保关闭）'));
        $this->line('  · 密钥：'.($hadSecret ? '已清除' : '无'));
        $this->line('  · 锁定：'.($wasLocked ? '已解除' : '无'));

        return self::SUCCESS;
    }
}
