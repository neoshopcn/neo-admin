<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Admin\Concerns\AppliesListFilters;
use App\Http\Controllers\Controller;
use App\Models\Miniprogram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MiniprogramController extends Controller
{
    use ApiResponse;
    use AppliesListFilters;

    public function index(Request $request): JsonResponse
    {
        $query = Miniprogram::query()->orderByDesc('id');

        $this->applyKeyword($query, $request, ['name', 'app_code', 'app_id']);
        $this->applyExact($query, $request, 'check_status', 'check_status');
        $this->applyExact($query, $request, 'status', 'status');
        $this->applyDateRange($query, $request, 'created_from', 'created_to', 'created_at');

        $pageSize = min(100, max(1, (int) $request->input('page_size', 15)));

        return $this->paginate($query->paginate($pageSize));
    }

    public function show(int $id): JsonResponse
    {
        $row = Miniprogram::query()->find($id);
        if (! $row) {
            return $this->fail('记录不存在', 404, 404);
        }

        return $this->ok($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:128'],
            'app_code' => ['required', 'string', 'max:32', Rule::unique('miniprograms', 'app_code')],
            'app_id' => ['required', 'string', 'max:64'],
            'app_secret' => ['nullable', 'string', 'max:128'],
            'token' => ['nullable', 'string', 'max:200'],
            'aes_key' => ['nullable', 'string', 'max:200'],
            'logo' => ['nullable', 'string', 'max:512'],
            'check_status' => ['required', 'integer', 'in:0,1,2'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $row = Miniprogram::query()->create([
            'name' => $data['name'],
            'app_code' => $data['app_code'],
            'app_id' => $data['app_id'],
            'app_secret' => $data['app_secret'] ?? '',
            'token' => $data['token'] ?? null,
            'aes_key' => $data['aes_key'] ?? null,
            'logo' => $data['logo'] ?? null,
            'check_status' => (int) $data['check_status'],
            'status' => (int) $data['status'],
        ]);

        return $this->ok(['id' => $row->id]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = Miniprogram::query()->find($id);
        if (! $row) {
            return $this->fail('记录不存在', 404, 404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:128'],
            'app_code' => ['required', 'string', 'max:32', Rule::unique('miniprograms', 'app_code')->ignore($row->id)],
            'app_id' => ['required', 'string', 'max:64'],
            'app_secret' => ['nullable', 'string', 'max:128'],
            'token' => ['nullable', 'string', 'max:200'],
            'aes_key' => ['nullable', 'string', 'max:200'],
            'logo' => ['nullable', 'string', 'max:512'],
            'check_status' => ['required', 'integer', 'in:0,1,2'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $row->name = $data['name'];
        $row->app_code = $data['app_code'];
        $row->app_id = $data['app_id'];
        if (array_key_exists('app_secret', $data) && $data['app_secret'] !== null) {
            $row->app_secret = $data['app_secret'];
        }
        $row->token = $data['token'] ?? null;
        $row->aes_key = $data['aes_key'] ?? null;
        $row->logo = $data['logo'] ?? null;
        $row->check_status = (int) $data['check_status'];
        $row->status = (int) $data['status'];
        $row->save();

        return $this->ok(true);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = Miniprogram::query()->find($id);
        if (! $row) {
            return $this->fail('记录不存在', 404, 404);
        }

        $row->delete();

        return $this->ok(true);
    }
}
