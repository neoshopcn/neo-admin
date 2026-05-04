<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Admin\Concerns\AppliesListFilters;
use App\Http\Controllers\Controller;
use App\Models\RecycleBinItem;
use App\Services\RecycleBin\RecycleBinRestorer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RecycleBinItemController extends Controller
{
    use ApiResponse;
    use AppliesListFilters;

    public function index(Request $request): JsonResponse
    {
        $query = $this->recycleBinItemQuery()
            ->with('operator:id,name,username')
            ->orderByDesc('recycled_at');

        $this->applyKeyword($query, $request, ['source_table', 'model_class']);

        if ($request->filled('source_table')) {
            $query->where('source_table', $request->input('source_table'));
        }

        $pageSize = min(100, max(1, (int) $request->input('page_size', 15)));

        $paginator = $query->paginate($pageSize);
        $paginator->getCollection()->transform(fn (RecycleBinItem $row) => $this->transformRow($row));

        return $this->paginate($paginator);
    }

    public function restore(int $id, RecycleBinRestorer $restorer): JsonResponse
    {
        $item = $this->recycleBinItemQuery()->find($id);
        if (! $item) {
            return $this->fail('记录不存在', 404, 404);
        }

        try {
            $restorer->restore($item);
        } catch (ValidationException $e) {
            return $this->fail($e->validator->errors()->first(), 422, 422);
        }

        return $this->ok(true);
    }

    public function purge(int $id, RecycleBinRestorer $restorer): JsonResponse
    {
        $item = $this->recycleBinItemQuery()->find($id);
        if (! $item) {
            return $this->fail('记录不存在', 404, 404);
        }

        $restorer->purge($item);

        return $this->ok(true);
    }

    /**
     * @return Builder<RecycleBinItem>
     */
    protected function recycleBinItemQuery(): Builder
    {
        $query = RecycleBinItem::query();
        $this->applyRecycleBinDataScope($query);

        return $query;
    }

    protected function applyRecycleBinDataScope(Builder $query): void
    {
        if ((int) auth()->id() !== 1) {
            $query->where('operator_id', auth()->id());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformRow(RecycleBinItem $row): array
    {
        $op = $row->operator;

        return [
            'id' => $row->id,
            'source_table' => $row->source_table,
            'model_class' => $row->model_class,
            'payload' => $row->payload,
            'payload_preview' => $this->payloadPreview($row->payload ?? []),
            'recycled_at' => $row->recycled_at?->format('Y-m-d H:i:s'),
            'operator_id' => $row->operator_id,
            'operator_label' => $op ? trim(($op->name ?: '').($op->username ? ' ('.$op->username.')' : '')) : '',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function payloadPreview(array $payload): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return '';
        }
        if (strlen($json) <= 280) {
            return $json;
        }

        return substr($json, 0, 277).'...';
    }
}
