<?php

namespace App\Support;

use App\Models\ConfigGroup;
use App\Models\ConfigItem;
use Illuminate\Support\Facades\Cache;

/**
 * 动态配置中心读取（带缓存）。
 *
 * 用法：
 * - ConfigCenter::group('api', 'wechat');
 * - ConfigCenter::section('api', 'wechat', 'pay');
 * - ConfigCenter::item('api', 'wechat', 'pay', 'mch_id');
 * - ConfigCenter::get('api.wechat.pay.mch_id');
 */
final class ConfigCenter
{
    private const CACHE_KEY = 'config_center:index';

    private const CACHE_TTL = 86400;

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, array<string, array<string, array<string, mixed>>>>
     */
    public static function index(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => self::buildIndex());
    }

    /**
     * 按组获取：section => [name => value]
     *
     * @return array<string, array<string, mixed>>
     */
    public static function group(string $page, string $group, array $default = []): array
    {
        $index = self::index();

        return ($index[$page] ?? [])[$group] ?? $default;
    }

    /**
     * 按分区获取：name => value
     *
     * @return array<string, mixed>
     */
    public static function section(string $page, string $group, string $section, array $default = []): array
    {
        $index = self::index();

        return (($index[$page] ?? [])[$group] ?? [])[$section] ?? $default;
    }

    /**
     * 按配置项获取
     */
    public static function item(string $page, string $group, string $section, string $name, mixed $default = null): mixed
    {
        return self::section($page, $group, $section)[$name] ?? $default;
    }

    /**
     * 点号路径：page.group | page.group.section | page.group.section.name
     */
    public static function get(string $path, mixed $default = null): mixed
    {
        $parts = explode('.', $path);

        return match (count($parts)) {
            2 => self::group($parts[0], $parts[1], is_array($default) ? $default : []),
            3 => self::section($parts[0], $parts[1], $parts[2], is_array($default) ? $default : []),
            4 => self::item($parts[0], $parts[1], $parts[2], $parts[3], $default),
            default => $default,
        };
    }

    /**
     * @return array<string, array<string, array<string, array<string, mixed>>>>
     */
    private static function buildIndex(): array
    {
        $index = [];

        $groups = ConfigGroup::query()
            ->where('status', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->with([
                'sections' => fn ($q) => $q->where('status', 1)->orderBy('sort')->orderBy('id'),
                'sections.items' => fn ($q) => $q->where('status', 1)->orderBy('sort')->orderBy('id'),
            ])
            ->get();

        foreach ($groups as $group) {
            $page = $group->page;
            $groupName = $group->name;

            foreach ($group->sections as $section) {
                $sectionItems = [];

                foreach ($section->items as $item) {
                    $sectionItems[$item->name] = self::castValue($item);
                }

                $index[$page][$groupName][$section->name] = $sectionItems;
            }
        }

        return $index;
    }

    private static function castValue(ConfigItem $item): mixed
    {
        $raw = $item->value ?? $item->default;

        if ($raw === null || $raw === '') {
            return null;
        }

        return match ($item->type) {
            'switch' => (int) $raw === 1,
            'number' => str_contains((string) $raw, '.') ? (float) $raw : (int) $raw,
            'json' => self::decodeJson((string) $raw),
            default => (string) $raw,
        };
    }

    /** @return array<string, mixed>|string|null */
    private static function decodeJson(string $raw): array|string|null
    {
        $decoded = json_decode($raw, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
    }
}
