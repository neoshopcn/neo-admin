<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Admin\Concerns\AppliesListFilters;
use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    use ApiResponse;
    use AppliesListFilters;

    public function index(Request $request): JsonResponse
    {
        $query = Resource::query()
            ->with('uploader:id,name,username')
            ->orderByDesc('id');

        $this->applyKeyword($query, $request, ['original_name', 'storage_path']);
        $this->applyExact($query, $request, 'scene', 'scene');
        $this->applyExact($query, $request, 'status', 'status');
        $this->applyExact($query, $request, 'disk', 'disk');
        $this->applyDateRange($query, $request, 'uploaded_from', 'uploaded_to', 'uploaded_at');

        if ($request->filled('file_type')) {
            $ft = trim((string) $request->input('file_type'));
            $query->where(function ($q) use ($ft) {
                $q->where('extension', $ft)->orWhere('mime_type', 'like', '%'.$ft.'%');
            });
        }

        $pageSize = min(100, max(1, (int) $request->input('page_size', 15)));

        $paginator = $query->paginate($pageSize);
        $paginator->getCollection()->transform(function (Resource $r) {
            return $this->transformRow($r);
        });

        return $this->paginate($paginator);
    }

    public function show(int $id): JsonResponse
    {
        $resource = Resource::query()->with('uploader:id,name,username')->find($id);
        if (! $resource) {
            return $this->fail('记录不存在', 404, 404);
        }

        return $this->ok($this->transformRow($resource));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $resource = Resource::query()->find($id);
        if (! $resource) {
            return $this->fail('记录不存在', 404, 404);
        }

        $data = $request->validate([
            'tags' => ['nullable'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $tagsRaw = $data['tags'] ?? null;
        if (is_array($tagsRaw)) {
            $resource->tags = array_values(array_filter(array_map(static fn ($v) => trim((string) $v), $tagsRaw)));
        } elseif ($tagsRaw === null || $tagsRaw === '') {
            $resource->tags = [];
        } else {
            $resource->tags = array_values(array_filter(array_map('trim', explode(',', (string) $tagsRaw))));
        }

        $resource->status = (int) $data['status'];
        $resource->save();

        return $this->ok(true);
    }

    public function destroy(int $id): JsonResponse
    {
        $resource = Resource::query()->find($id);
        if (! $resource) {
            return $this->fail('记录不存在', 404, 404);
        }

        $resource->status = 0;
        $resource->save();

        return $this->ok(true);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformRow(Resource $r): array
    {
        $row = $r->toArray();
        $row['public_url'] = $r->public_url;
        $row['size_label'] = $r->size_label;
        $row['tags_display'] = $r->tags_display;
        $row['uploader_name'] = $r->uploader
            ? (($r->uploader->name ?: '').($r->uploader->username ? ' ('.$r->uploader->username.')' : ''))
            : '';

        return $row;
    }
}
