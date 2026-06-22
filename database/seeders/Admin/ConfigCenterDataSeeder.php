<?php

namespace Database\Seeders\Admin;

use App\Models\ConfigGroup;
use App\Models\ConfigItem;
use App\Models\ConfigSection;
use App\Support\ConfigCenter;
use Illuminate\Database\Seeder;

class ConfigCenterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSystemConfig();
        $this->seedApiConfig();

        ConfigCenter::forgetCache();
    }

    private function seedSystemConfig(): void
    {
        $site = $this->syncGroup(ConfigGroup::PAGE_SYSTEM, 'site', '站点配置', 'Monitor', 10);
        $siteSection = $this->syncSection($site, 'default', '默认', null, 10);
        $this->syncItems($site, $siteSection, [
            ['site_name', '站点名称', 'text', 'Neo Admin', null, null, 1, 10],
            ['site_logo', '站点 Logo', 'text', '', null, 'url', 0, 20],
            ['site_icp', 'ICP 备案号', 'text', '', null, null, 0, 30],
        ]);

        $login = $this->syncGroup(ConfigGroup::PAGE_SYSTEM, 'login', '登录配置', 'User', 20);
        $loginSection = $this->syncSection($login, 'default', '默认', null, 10);
        $this->syncItems($login, $loginSection, [
            ['login_captcha', '登录验证码', 'switch', '1', null, null, 0, 10],
            ['session_lifetime', '会话时长（分钟）', 'number', '120', null, 'min:5|max:10080', 0, 20],
            ['password_min_length', '密码最小长度', 'number', '8', null, 'min:6|max:32', 0, 30],
        ]);

        $security = $this->syncGroup(ConfigGroup::PAGE_SYSTEM, 'security', '安全配置', 'Lock', 30);
        $securitySection = $this->syncSection($security, 'default', '默认', null, 10);
        $this->syncItems($security, $securitySection, [
            ['ip_whitelist', 'IP白名单', 'textarea', '', null, null, 0, 10],
            ['keyword_filter', '关键词过滤', 'textarea', '', null, null, 0, 20],
        ]);

        $this->prunePageGroups(ConfigGroup::PAGE_SYSTEM, ['site', 'login', 'security']);
    }

    private function seedApiConfig(): void
    {
        $this->seedSmsConfig();
        $this->seedEmailConfig();
        $this->seedWechatConfig();
        $this->seedMapConfig();
        $this->seedRealnameConfig();
    }

    private function seedEmailConfig(): void
    {
        $group = $this->syncGroup(ConfigGroup::PAGE_API, 'email', '邮件配置', 'MessageBox', 15);

        $default = $this->syncSection($group, 'default', '默认', 'Setting', 10);
        $this->syncItems($group, $default, [
            ['smtp_host', 'SMTP服务器地址', 'text', '', null, null, 1, 10],
            ['smtp_port', 'SMTP端口', 'number', '465', null, 'min:1|max:65535', 1, 20],
            ['smtp_username', '发件人账号', 'text', '', null, null, 1, 30],
            ['smtp_password', '密码(授权码)', 'password', '', null, null, 1, 40],
            ['from_name', '发件人昵称', 'text', '', null, null, 0, 50],
        ]);
    }

    private function seedSmsConfig(): void
    {
        $group = $this->syncGroup(ConfigGroup::PAGE_API, 'sms', '短信配置', 'Message', 10);

        $aliyun = $this->syncSection($group, 'aliyun', '阿里云短信', 'Promotion', 10);
        $this->syncItems($group, $aliyun, [
            ['access_key_id', 'AccessKey ID', 'text', '', null, null, 1, 10],
            ['access_key_secret', 'AccessKey Secret', 'password', '', null, null, 1, 20],
            ['sign_name', '短信签名', 'text', '', null, null, 1, 30],
            ['template_code', '默认模板 Code', 'text', '', null, null, 0, 40],
        ]);

        $tencent = $this->syncSection($group, 'tencent', '腾讯云短信', 'ChatDotRound', 20);
        $this->syncItems($group, $tencent, [
            ['secret_id', 'SecretId', 'text', '', null, null, 1, 10],
            ['secret_key', 'SecretKey', 'password', '', null, null, 1, 20],
            ['sdk_app_id', 'SDK AppID', 'text', '', null, null, 1, 30],
            ['sign_name', '短信签名', 'text', '', null, null, 1, 40],
        ]);
    }

    private function seedWechatConfig(): void
    {
        $group = $this->syncGroup(ConfigGroup::PAGE_API, 'wechat', '微信配置', 'ChatDotRound', 20);

        $mini = $this->syncSection($group, 'mini', '小程序配置', 'Iphone', 10);
        $this->syncItems($group, $mini, [
            ['appid', 'AppID', 'text', '', null, null, 1, 10],
            ['secret', 'AppSecret', 'password', '', null, null, 1, 20],
            ['token', 'Token', 'text', '', null, null, 0, 30],
            ['encoding_aes_key', 'EncodingAESKey', 'password', '', null, null, 0, 40],
        ]);

        $mp = $this->syncSection($group, 'mp', '公众号配置', 'ChatLineRound', 20);
        $this->syncItems($group, $mp, [
            ['appid', 'AppID', 'text', '', null, null, 1, 10],
            ['secret', 'AppSecret', 'password', '', null, null, 1, 20],
            ['token', 'Token', 'text', '', null, null, 0, 30],
            ['encoding_aes_key', 'EncodingAESKey', 'password', '', null, null, 0, 40],
        ]);

        $pay = $this->syncSection($group, 'pay', '微信支付', 'Wallet', 30);
        $this->syncItems($group, $pay, [
            ['mch_id', '商户号', 'text', '', null, null, 1, 10],
            ['secret_key', 'APIv3 密钥', 'password', '', null, null, 1, 20],
            ['platform_cert_id', '证书序列号', 'text', '', null, null, 1, 30],
            ['private_key', '商户API私钥PEM', 'textarea', '', null, null, 1, 40],
            ['certificate', '商户API证书PEM', 'textarea', '', null, null, 1, 50],
            ['platform_cert', '平台公钥证书PEM', 'textarea', '', null, null, 1, 60],
            ['notify_url', '支付回调地址', 'text', '', null, 'url', 1, 70],
        ]);
    }

    private function seedMapConfig(): void
    {
        $group = $this->syncGroup(ConfigGroup::PAGE_API, 'map', '地图配置', 'Location', 30);

        $amap = $this->syncSection($group, 'amap', '高德地图', 'MapLocation', 10);
        $this->syncItems($group, $amap, [
            ['key', 'Key', 'text', '', null, null, 1, 10],
            ['security_js_code', 'SecurityJsCode', 'password', '', null, null, 1, 20],
        ]);

        $bmap = $this->syncSection($group, 'bmap', '百度地图', 'Place', 20);
        $this->syncItems($group, $bmap, [
            ['ak', 'Access Key', 'text', '', null, null, 1, 10],
        ]);
    }

    private function seedRealnameConfig(): void
    {
        $group = $this->syncGroup(ConfigGroup::PAGE_API, 'realname', '实名配置', 'UserFilled', 40);

        $aliyun = $this->syncSection($group, 'aliyun', '阿里云实名认证', 'Checked', 10);
        $this->syncItems($group, $aliyun, [
            ['app_key', 'AppKey', 'text', '', null, null, 1, 10],
            ['app_secret', 'AppSecret', 'password', '', null, null, 1, 20],
            ['app_code', 'AppCode', 'text', '', null, null, 1, 30],
        ]);

        $tencent = $this->syncSection($group, 'tencent', '腾讯云实名认证', 'Avatar', 20);
        $this->syncItems($group, $tencent, [
            ['secret_id', 'SecretId', 'text', '', null, null, 1, 10],
            ['secret_key', 'SecretKey', 'password', '', null, null, 1, 20],
        ]);
    }

    private function syncGroup(string $page, string $name, string $label, ?string $icon, int $sort): ConfigGroup
    {
        /** @var ConfigGroup $group */
        $group = ConfigGroup::query()->updateOrCreate(
            ['page' => $page, 'name' => $name],
            [
                'label' => $label,
                'icon' => $icon,
                'sort' => $sort,
                'status' => 1,
            ]
        );

        return $group;
    }

    private function syncSection(ConfigGroup $group, string $name, string $label, ?string $icon, int $sort): ConfigSection
    {
        /** @var ConfigSection $section */
        $section = ConfigSection::query()->updateOrCreate(
            ['group_id' => $group->id, 'name' => $name],
            [
                'label' => $label,
                'icon' => $icon,
                'sort' => $sort,
                'status' => 1,
            ]
        );

        return $section;
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: string, 3: string, 4: mixed, 5: ?string, 6: int, 7: int}>  $rows
     */
    private function syncItems(ConfigGroup $group, ConfigSection $section, array $rows): void
    {
        $names = [];

        foreach ($rows as [$name, $label, $type, $default, $options, $rules, $required, $sort]) {
            $names[] = $name;
            ConfigItem::query()->updateOrCreate(
                ['section_id' => $section->id, 'name' => $name],
                [
                    'group_id' => $group->id,
                    'label' => $label,
                    'type' => $type,
                    'default' => $default,
                    'options' => $options,
                    'rules' => $rules,
                    'required' => $required,
                    'sort' => $sort,
                    'status' => 1,
                ]
            );
        }

        ConfigItem::query()
            ->where('section_id', $section->id)
            ->whereNotIn('name', $names)
            ->delete();
    }

    /** @param  array<int, string>  $groupNames */
    private function prunePageGroups(string $page, array $groupNames): void
    {
        $obsoleteGroups = ConfigGroup::query()
            ->where('page', $page)
            ->whereNotIn('name', $groupNames)
            ->get();

        foreach ($obsoleteGroups as $group) {
            ConfigItem::query()->where('group_id', $group->id)->delete();
            ConfigSection::query()->where('group_id', $group->id)->delete();
            $group->delete();
        }
    }
}
