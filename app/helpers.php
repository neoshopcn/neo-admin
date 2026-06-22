<?php

use App\Support\ConfigCenter;

if (! function_exists('config_center')) {
    /**
     * 按点号路径获取动态配置中心项。
     *
     * page.group.section.name | page.group.section | page.group
     *
     * @example config_center('api.wechat.pay.mch_id')
     * @example config_center('system.login.default.login_captcha', false)
     */
    function config_center(string $path, mixed $default = null): mixed
    {
        return ConfigCenter::get($path, $default);
    }
}
