<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** 操作日志页 */
class OperationLogPageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.content.logs', [
            'neo' => [
                'title' => '操作日志',
                'listUrl' => url('/admin/api/operation-logs'),
                'batchDeleteUrl' => url('/admin/api/operation-logs/batch-delete'),
                'rowKey' => 'id',
                'columns' => [
                    ['prop' => 'id', 'label' => 'ID', 'width' => 72],
                    ['prop' => 'username', 'label' => '用户', 'minWidth' => 100],
                    ['prop' => 'method', 'label' => '方法', 'width' => 80],
                    ['prop' => 'path', 'label' => '路径', 'minWidth' => 200],
                    ['prop' => 'ip', 'label' => 'IP', 'width' => 130],
                    ['prop' => 'action', 'label' => '路由', 'minWidth' => 160],
                    ['prop' => 'created_at', 'label' => '时间', 'minWidth' => 168],
                ],
                'filters' => [
                    ['type' => 'input', 'key' => 'keyword', 'placeholder' => '用户 / 路径 / IP'],
                    ['type' => 'daterange', 'startKey' => 'created_from', 'endKey' => 'created_to'],
                ],
                'selectionEnabled' => true,
                'batchActions' => [
                    ['key' => 'delete', 'label' => '批量删除', 'perm' => 'log:delete', 'btnType' => 'danger', 'btnPlain' => false],
                ],
                'hideActions' => true,
            ],
        ]);
    }
}
