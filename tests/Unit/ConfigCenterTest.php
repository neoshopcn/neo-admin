<?php

namespace Tests\Unit;

use App\Models\ConfigGroup;
use App\Models\ConfigItem;
use App\Models\ConfigSection;
use App\Support\ConfigCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConfigCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ConfigCenter::forgetCache();
        $this->seedSampleConfig();
    }

    public function test_item_reads_value_and_default(): void
    {
        $this->assertSame('Neo Admin', ConfigCenter::item('system', 'site', 'default', 'site_name'));
        $this->assertSame('wx_appid_value', ConfigCenter::item('api', 'wechat', 'mini', 'appid'));
    }

    public function test_item_returns_default_when_missing(): void
    {
        $this->assertSame('fallback', ConfigCenter::item('api', 'wechat', 'mini', 'not_exists', 'fallback'));
    }

    public function test_section_returns_items_in_section(): void
    {
        $this->assertSame([
            'appid' => 'wx_appid_value',
            'secret' => 'wx_secret_value',
            'enabled' => true,
        ], ConfigCenter::section('api', 'wechat', 'mini'));
    }

    public function test_group_returns_all_sections(): void
    {
        $this->assertSame([
            'mini' => [
                'appid' => 'wx_appid_value',
                'secret' => 'wx_secret_value',
                'enabled' => true,
            ],
            'pay' => [
                'mch_id' => '1900000109',
                'fee_rate' => 0.6,
            ],
        ], ConfigCenter::group('api', 'wechat'));
    }

    public function test_get_supports_dot_paths(): void
    {
        $this->assertSame('wx_appid_value', ConfigCenter::get('api.wechat.mini.appid'));
        $this->assertSame([
            'appid' => 'wx_appid_value',
            'secret' => 'wx_secret_value',
            'enabled' => true,
        ], ConfigCenter::get('api.wechat.mini'));
        $this->assertSame([
            'mini' => [
                'appid' => 'wx_appid_value',
                'secret' => 'wx_secret_value',
                'enabled' => true,
            ],
            'pay' => [
                'mch_id' => '1900000109',
                'fee_rate' => 0.6,
            ],
        ], ConfigCenter::get('api.wechat'));
    }

    public function test_get_returns_default_for_invalid_path(): void
    {
        $this->assertSame('none', ConfigCenter::get('api.wechat.mini.not_exists', 'none'));
        $this->assertSame('none', ConfigCenter::get('invalid', 'none'));
    }

    public function test_helper_config_center_works(): void
    {
        $this->assertSame('Neo Admin', config_center('system.site.default.site_name'));
        $this->assertSame('1900000109', config_center('api.wechat.pay.mch_id'));
        $this->assertSame(['retry' => 3], config_center('system.site.default.meta_json'));
    }

    public function test_value_types_are_cast(): void
    {
        $this->assertIsBool(ConfigCenter::item('api', 'wechat', 'mini', 'enabled'));
        $this->assertTrue(ConfigCenter::item('api', 'wechat', 'mini', 'enabled'));
        $this->assertSame(0.6, ConfigCenter::item('api', 'wechat', 'pay', 'fee_rate'));
        $this->assertSame(['retry' => 3], ConfigCenter::item('system', 'site', 'default', 'meta_json'));
    }

    public function test_cache_is_used_until_forget(): void
    {
        $this->assertSame('wx_appid_value', ConfigCenter::item('api', 'wechat', 'mini', 'appid'));

        DB::table('config_items')
            ->where('name', 'appid')
            ->update(['value' => 'updated_without_forget']);

        $this->assertSame('wx_appid_value', ConfigCenter::item('api', 'wechat', 'mini', 'appid'));

        ConfigCenter::forgetCache();

        $this->assertSame('updated_without_forget', ConfigCenter::item('api', 'wechat', 'mini', 'appid'));
    }

    public function test_index_is_stored_in_cache(): void
    {
        ConfigCenter::index();

        $this->assertTrue(Cache::has('config_center:index'));
    }

    private function seedSampleConfig(): void
    {
        $siteGroup = ConfigGroup::query()->create([
            'page' => ConfigGroup::PAGE_SYSTEM,
            'name' => 'site',
            'label' => '站点配置',
            'icon' => 'Monitor',
            'sort' => 10,
            'status' => 1,
        ]);

        $siteSection = ConfigSection::query()->create([
            'group_id' => $siteGroup->id,
            'name' => 'default',
            'label' => '默认',
            'icon' => null,
            'sort' => 10,
            'status' => 1,
        ]);

        $this->createItem($siteGroup, $siteSection, [
            'name' => 'site_name',
            'label' => '站点名称',
            'type' => 'text',
            'value' => null,
            'default' => 'Neo Admin',
            'sort' => 10,
        ]);

        $this->createItem($siteGroup, $siteSection, [
            'name' => 'meta_json',
            'label' => '元数据',
            'type' => 'json',
            'value' => '{"retry":3}',
            'default' => null,
            'sort' => 20,
        ]);

        $wechatGroup = ConfigGroup::query()->create([
            'page' => ConfigGroup::PAGE_API,
            'name' => 'wechat',
            'label' => '微信配置',
            'icon' => 'ChatDotRound',
            'sort' => 20,
            'status' => 1,
        ]);

        $miniSection = ConfigSection::query()->create([
            'group_id' => $wechatGroup->id,
            'name' => 'mini',
            'label' => '小程序配置',
            'icon' => null,
            'sort' => 10,
            'status' => 1,
        ]);

        $this->createItem($wechatGroup, $miniSection, [
            'name' => 'appid',
            'label' => 'AppID',
            'type' => 'text',
            'value' => 'wx_appid_value',
            'default' => '',
            'sort' => 10,
        ]);

        $this->createItem($wechatGroup, $miniSection, [
            'name' => 'secret',
            'label' => 'AppSecret',
            'type' => 'password',
            'value' => 'wx_secret_value',
            'default' => '',
            'sort' => 20,
        ]);

        $this->createItem($wechatGroup, $miniSection, [
            'name' => 'enabled',
            'label' => '启用',
            'type' => 'switch',
            'value' => '1',
            'default' => '0',
            'sort' => 30,
        ]);

        $paySection = ConfigSection::query()->create([
            'group_id' => $wechatGroup->id,
            'name' => 'pay',
            'label' => '微信支付',
            'icon' => null,
            'sort' => 20,
            'status' => 1,
        ]);

        $this->createItem($wechatGroup, $paySection, [
            'name' => 'mch_id',
            'label' => '商户号',
            'type' => 'text',
            'value' => '1900000109',
            'default' => '',
            'sort' => 10,
        ]);

        $this->createItem($wechatGroup, $paySection, [
            'name' => 'fee_rate',
            'label' => '费率',
            'type' => 'number',
            'value' => '0.6',
            'default' => '0',
            'sort' => 20,
        ]);
    }

    /** @param  array<string, mixed>  $fields */
    private function createItem(ConfigGroup $group, ConfigSection $section, array $fields): void
    {
        ConfigItem::query()->create(array_merge([
            'group_id' => $group->id,
            'section_id' => $section->id,
            'options' => null,
            'rules' => null,
            'required' => 0,
            'status' => 1,
        ], $fields));
    }
}
