<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Admin\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** 文档示例假数据接口 */
class DocDemoController extends Controller
{
    use ApiResponse;

    /** 分页列表示例 */
    public function tableDemo(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->input('keyword', ''));
        $overrides = session('doc_demo_row_overrides', []);

        $all = collect(range(1, 47))->map(function (int $i) use ($overrides) {
            $row = [
                'id' => $i,
                'sort_order' => $i * 10,
                'title' => '示例项目 '.$i,
                'status' => $i % 3 === 0 ? 0 : 1,
                'updated_at' => now()->subDays($i % 14)->format('Y-m-d H:i:s'),
            ];
            if (isset($overrides[$i]) && is_array($overrides[$i])) {
                $row = array_merge($row, $overrides[$i]);
            }

            return $row;
        });

        if ($keyword !== '') {
            $all = $all->filter(fn (array $r) => str_contains($r['title'], $keyword))->values();
        }

        $page = max(1, (int) $request->input('page', 1));
        $pageSize = min(50, max(1, (int) $request->input('page_size', 15)));
        $total = $all->count();
        $list = $all->forPage($page, $pageSize)->values()->all();

        return $this->ok([
            'list' => $list,
            'pagination' => [
                'page' => $page,
                'page_size' => $pageSize,
                'total' => $total,
            ],
        ]);
    }

    /** 演示合并写入 Session */
    public function patchDemoRow(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'min:1', 'max:99999'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        $overrides = session('doc_demo_row_overrides', []);
        $id = $data['id'];
        $patch = $overrides[$id] ?? [];
        $patch['status'] = (int) $data['status'];
        $overrides[$id] = $patch;
        session(['doc_demo_row_overrides' => $overrides]);

        return $this->ok(true);
    }
}
