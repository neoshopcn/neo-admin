<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Admin\Concerns\AppliesListFilters;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Google2faService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponse;
    use AppliesListFilters;

    public function index(Request $request): JsonResponse
    {
        $query = User::query()->with('roles:id,name')->orderByDesc('id');

        $this->applyKeyword($query, $request, ['username', 'name', 'email', 'avatar']);
        $this->applyExact($query, $request, 'status', 'status');
        $this->applyDateRange($query, $request, 'created_from', 'created_to', 'created_at');

        if ($request->filled('role_id')) {
            $rid = (int) $request->input('role_id');
            $query->whereHas('roles', fn ($q) => $q->where('roles.id', $rid));
        }

        $pageSize = min(100, max(1, (int) $request->input('page_size', 15)));

        return $this->paginate($query->paginate($pageSize));
    }

    public function show(int $id): JsonResponse
    {
        $user = User::query()->with('roles:id,name')->find($id);
        if (! $user) {
            return $this->fail('记录不存在', 404, 404);
        }

        return $this->ok($user);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:64', Rule::unique('users', 'username')],
            'name' => ['required', 'string', 'max:120'],
            'avatar' => ['nullable', 'string', 'max:512'],
            'email' => ['nullable', 'string', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:6', 'max:64'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $user = User::query()->create([
            'username' => $data['username'],
            'name' => $data['name'],
            'avatar' => $data['avatar'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'status' => (int) $data['status'],
        ]);

        $user->roles()->sync($this->normalizeRoleIds($data['role_ids'] ?? []));

        return $this->ok(['id' => $user->id]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::query()->find($id);
        if (! $user) {
            return $this->fail('记录不存在', 404, 404);
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'max:64', Rule::unique('users', 'username')->ignore($user->id)],
            'name' => ['required', 'string', 'max:120'],
            'avatar' => ['nullable', 'string', 'max:512'],
            'email' => ['nullable', 'string', 'email', 'max:190'],
            'password' => ['nullable', 'string', 'min:6', 'max:64'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $user->username = $data['username'];
        $user->name = $data['name'];
        $user->avatar = $data['avatar'] ?? null;
        $user->email = $data['email'] ?? null;
        $user->status = (int) $data['status'];
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        if (array_key_exists('role_ids', $data)) {
            $user->roles()->sync($this->normalizeRoleIds($data['role_ids'] ?? []));
        }

        return $this->ok(true);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($id === $request->user()->id) {
            return $this->fail('不能删除当前登录账号');
        }

        $user = User::query()->find($id);
        if (! $user) {
            return $this->fail('记录不存在', 404, 404);
        }

        $user->delete();

        return $this->ok(true);
    }

    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $user = User::query()->find($id);
        if (! $user) {
            return $this->fail('记录不存在', 404, 404);
        }

        $data = $request->validate([
            'password' => ['nullable', 'string', 'min:6', 'max:64'],
        ]);

        $pwd = $data['password'] ?? substr(str_shuffle('abcdefghjkmnpqrstuvwxyz23456789'), 0, 10);
        $user->password = $pwd;
        $user->save();

        return $this->ok(['password_plain' => $pwd]);
    }

    public function enableGoogle2fa(int $id, Google2faService $google2fa): JsonResponse
    {
        $user = User::query()->find($id);
        if (! $user) {
            return $this->fail('记录不存在', 404, 404);
        }

        if ($user->isGoogle2faEnabled()) {
            return $this->fail('该用户已开启双因子验证');
        }

        $payload = $google2fa->enable($user);

        return $this->ok($payload);
    }

    public function disableGoogle2fa(int $id, Google2faService $google2fa): JsonResponse
    {
        $user = User::query()->find($id);
        if (! $user) {
            return $this->fail('记录不存在', 404, 404);
        }

        if (! $user->isGoogle2faEnabled()) {
            return $this->fail('该用户未开启双因子验证');
        }

        $google2fa->disable($user);

        return $this->ok(true);
    }

    public function unlockGoogle2fa(int $id, Google2faService $google2fa): JsonResponse
    {
        $user = User::query()->find($id);
        if (! $user) {
            return $this->fail('记录不存在', 404, 404);
        }

        if (! $user->isGoogle2faLocked()) {
            return $this->fail('该用户未被锁定');
        }

        $google2fa->unlock($user);

        return $this->ok(true);
    }

    /**
     * @param  array<int, mixed>  $roleIds
     * @return array<int, int>
     */
    protected function normalizeRoleIds(array $roleIds): array
    {
        $ids = array_map('intval', $roleIds);

        return array_values(array_unique(array_filter($ids, fn (int $id) => $id > 0)));
    }
}
