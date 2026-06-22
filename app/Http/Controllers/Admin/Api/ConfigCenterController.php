<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ConfigGroup;
use App\Models\ConfigItem;
use App\Support\ConfigCenter as ConfigCenterSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ConfigCenterController extends Controller
{
    use ApiResponse;

    public function showSystem(Request $request): JsonResponse
    {
        return $this->show($request, ConfigGroup::PAGE_SYSTEM);
    }

    public function showApi(Request $request): JsonResponse
    {
        return $this->show($request, ConfigGroup::PAGE_API);
    }

    public function updateSystemValues(Request $request): JsonResponse
    {
        return $this->updateValues($request, ConfigGroup::PAGE_SYSTEM);
    }

    public function updateApiValues(Request $request): JsonResponse
    {
        return $this->updateValues($request, ConfigGroup::PAGE_API);
    }

    public function show(Request $request, string $page): JsonResponse
    {
        if (! $this->isValidPage($page)) {
            return $this->fail('无效的配置页面', 404, 404);
        }

        $groups = ConfigGroup::query()
            ->where('page', $page)
            ->where('status', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->with([
                'sections' => fn ($q) => $q->where('status', 1)->orderBy('sort')->orderBy('id'),
                'sections.items' => fn ($q) => $q->where('status', 1)->orderBy('sort')->orderBy('id'),
            ])
            ->get();

        $payload = $groups->map(function (ConfigGroup $group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'label' => $group->label,
                'icon' => $group->icon,
                'sections' => $group->sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'name' => $section->name,
                        'label' => $section->label,
                        'icon' => $section->icon,
                        'items' => $section->items->map(fn (ConfigItem $item) => $this->formatItem($item))->values(),
                    ];
                })->values(),
            ];
        })->values();

        return $this->ok(['groups' => $payload]);
    }

    public function updateValues(Request $request, string $page): JsonResponse
    {
        if (! $this->isValidPage($page)) {
            return $this->fail('无效的配置页面', 404, 404);
        }

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.value' => ['nullable'],
        ]);

        $ids = collect($data['items'])->pluck('id')->unique()->map(fn ($v) => (int) $v)->all();
        $items = ConfigItem::query()
            ->whereIn('id', $ids)
            ->whereHas('group', fn ($q) => $q->where('page', $page))
            ->get()
            ->keyBy('id');

        if ($items->count() !== count($ids)) {
            return $this->fail('存在无效的配置项');
        }

        $errors = [];
        $updates = [];

        foreach ($data['items'] as $index => $row) {
            $id = (int) $row['id'];
            /** @var ConfigItem $item */
            $item = $items->get($id);
            $value = $row['value'] ?? null;

            try {
                $normalized = $this->normalizeValue($item, $value);
            } catch (ValidationException $e) {
                $errors["items.{$index}.value"] = $e->validator->errors()->first();

                continue;
            }

            $updates[$id] = $normalized;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($updates, $items) {
            foreach ($updates as $id => $value) {
                /** @var ConfigItem $item */
                $item = $items->get($id);
                $item->update(['value' => $value]);
            }
        });

        ConfigCenterSupport::forgetCache();

        return $this->ok(true);
    }

    private function isValidPage(string $page): bool
    {
        return in_array($page, [ConfigGroup::PAGE_SYSTEM, ConfigGroup::PAGE_API], true);
    }

    /** @return array<string, mixed> */
    private function formatItem(ConfigItem $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'label' => $item->label,
            'type' => $item->type,
            'value' => $item->value ?? $item->default ?? '',
            'default' => $item->default ?? '',
            'options' => $item->options ?? [],
            'required' => (int) $item->required,
        ];
    }

    /**
     * @throws ValidationException
     */
    private function normalizeValue(ConfigItem $item, mixed $value): ?string
    {
        $rules = $this->buildRules($item);

        $validated = validator(
            ['value' => $value],
            ['value' => $rules],
            [],
            ['value' => $item->label]
        )->validate();

        $normalized = $validated['value'];

        if ($normalized === null || $normalized === '') {
            return null;
        }

        if ($item->type === 'switch') {
            return (string) ((int) $normalized);
        }

        if ($item->type === 'json') {
            if (is_array($normalized)) {
                return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            }

            return (string) $normalized;
        }

        return (string) $normalized;
    }

    /** @return array<int, mixed> */
    private function buildRules(ConfigItem $item): array
    {
        $rules = [];

        if ((int) $item->required === 1) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        switch ($item->type) {
            case 'password':
            case 'text':
            case 'textarea':
                $rules[] = 'string';
                $rules[] = 'max:65535';
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'switch':
                $rules[] = 'integer';
                $rules[] = Rule::in([0, 1]);
                break;
            case 'select':
                $rules[] = 'string';
                $rules[] = 'max:255';
                $options = collect($item->options ?? [])->pluck('value')->filter()->all();
                if ($options !== []) {
                    $rules[] = Rule::in($options);
                }
                break;
            case 'json':
                $rules[] = function (string $attribute, mixed $val, \Closure $fail) {
                    if ($val === null || $val === '') {
                        return;
                    }
                    if (is_array($val)) {
                        return;
                    }
                    if (! is_string($val)) {
                        $fail('必须是合法 JSON');

                        return;
                    }
                    json_decode($val, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('必须是合法 JSON');
                    }
                };
                break;
            default:
                $rules[] = 'string';
                $rules[] = 'max:65535';
        }

        if ($item->rules) {
            foreach (explode('|', $item->rules) as $rule) {
                $rule = trim($rule);
                if ($rule !== '') {
                    $rules[] = $rule;
                }
            }
        }

        return $rules;
    }
}
