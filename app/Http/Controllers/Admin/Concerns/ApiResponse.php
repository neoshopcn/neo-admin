<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function ok(mixed $data = null, string $message = 'success'): JsonResponse
    {
        return response()->json([
            'code' => 0,
            'message' => $message,
            'data' => $data,
        ]);
    }

    protected function fail(string $message = 'error', int $code = 1, int $http = 400): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => null,
        ], $http);
    }

    protected function paginate(LengthAwarePaginator $paginator): JsonResponse
    {
        return $this->ok([
            'list' => $paginator->items(),
            'pagination' => [
                'page' => $paginator->currentPage(),
                'page_size' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
