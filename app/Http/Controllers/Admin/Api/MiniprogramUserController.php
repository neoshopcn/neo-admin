<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Admin\Concerns\AppliesListFilters;
use App\Http\Controllers\Controller;
use App\Models\MiniprogramUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MiniprogramUserController extends Controller
{
    use ApiResponse;
    use AppliesListFilters;

    public function index(Request $request): JsonResponse
    {
        $query = MiniprogramUser::query()->orderByDesc('id');

        $this->applyKeyword($query, $request, [
            'openid',
            'unionid',
            'app_code',
            'nick_name',
            'phone',
            'member_name',
            'remark',
            'tag',
        ]);
        $this->applyExact($query, $request, 'app_code', 'app_code');
        $this->applyExact($query, $request, 'is_disabled', 'is_disabled');
        $this->applyExact($query, $request, 'is_manager', 'is_manager');
        $this->applyDateRange($query, $request, 'created_from', 'created_to', 'created_at');

        $pageSize = min(100, max(1, (int) $request->input('page_size', 15)));

        return $this->paginate($query->paginate($pageSize));
    }

    public function show(int $id): JsonResponse
    {
        $row = MiniprogramUser::query()->find($id);
        if (! $row) {
            return $this->fail('记录不存在', 404, 404);
        }

        return $this->ok($row);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = MiniprogramUser::query()->find($id);
        if (! $row) {
            return $this->fail('记录不存在', 404, 404);
        }

        $data = $request->validate([
            'nick_name' => ['nullable', 'string', 'max:255'],
            'member_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'remark' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:255'],
            'm_level' => ['required', 'integer', 'min:0'],
            'm_balance' => ['required', 'numeric', 'min:0'],
            'm_points' => ['required', 'numeric', 'min:0'],
            'is_disabled' => ['required', Rule::in(['false', 'true'])],
            'is_manager' => ['required', Rule::in(['false', 'true'])],
        ]);

        $row->nick_name = $data['nick_name'] ?? null;
        $row->member_name = $data['member_name'] ?? null;
        $row->phone = $data['phone'] ?? null;
        $row->remark = $data['remark'] ?? null;
        $row->tag = $data['tag'] ?? null;
        $row->gender = $data['gender'] ?? $row->gender;
        $row->m_level = (int) $data['m_level'];
        $row->m_balance = (float) $data['m_balance'];
        $row->m_points = (float) $data['m_points'];
        $row->is_disabled = $data['is_disabled'];
        $row->is_manager = $data['is_manager'];
        $row->save();

        return $this->ok(true);
    }
}
