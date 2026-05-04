<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Admin\Concerns\AppliesListFilters;
use App\Http\Controllers\Controller;
use App\Models\AdminOperationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationLogController extends Controller
{
    use ApiResponse;
    use AppliesListFilters;

    public function index(Request $request): JsonResponse
    {
        $query = AdminOperationLog::query()->orderByDesc('id');

        $this->applyKeyword($query, $request, ['username', 'path', 'action', 'ip']);
        $this->applyDateRange($query, $request, 'created_from', 'created_to', 'created_at');

        $pageSize = min(100, max(1, (int) $request->input('page_size', 15)));

        return $this->paginate($query->paginate($pageSize));
    }

    public function batchDestroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'min:1'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['ids'])));
        $deleted = AdminOperationLog::query()->whereIn('id', $ids)->delete();

        return $this->ok(['deleted' => $deleted]);
    }
}
