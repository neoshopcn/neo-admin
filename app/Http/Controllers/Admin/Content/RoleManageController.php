<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** 角色与菜单授权页 */
class RoleManageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.content.roles', [
            'menuTreeUrl' => url('/admin/api/menus/tree'),
            'assignUrl' => url('/admin/api/roles'),
            'neo' => [
                'title' => '角色管理',
                'listUrl' => url('/admin/api/roles'),
                'rowKey' => 'id',
                'columns' => [
                    ['prop' => 'id', 'label' => 'ID', 'width' => 72],
                    ['prop' => 'name', 'label' => '名称', 'minWidth' => 140],
                    ['prop' => 'code', 'label' => '编码', 'minWidth' => 140],
                    ['prop' => 'status', 'label' => '状态', 'width' => 88, 'tag' => true],
                    ['prop' => 'created_at', 'label' => '创建时间', 'minWidth' => 168],
                ],
                'filters' => [
                    ['type' => 'input', 'key' => 'keyword', 'placeholder' => '名称 / 编码'],
                    [
                        'type' => 'select',
                        'key' => 'status',
                        'placeholder' => '状态',
                        'clearable' => true,
                        'options' => [['label' => '启用', 'value' => 1], ['label' => '禁用', 'value' => 0]],
                    ],
                    ['type' => 'daterange', 'startKey' => 'created_from', 'endKey' => 'created_to'],
                ],
                'formFields' => [
                    ['prop' => 'name', 'label' => '名称', 'rule' => ['required' => true]],
                    ['prop' => 'code', 'label' => '编码', 'rule' => ['required' => true]],
                    [
                        'prop' => 'status',
                        'label' => '状态',
                        'input' => 'select',
                        'options' => [['label' => '启用', 'value' => 1], ['label' => '禁用', 'value' => 0]],
                        'rule' => ['required' => true],
                    ],
                ],
                'perms' => [
                    'create' => 'role:create',
                    'edit' => 'role:edit',
                    'delete' => 'role:delete',
                    'view' => 'role:view',
                    'extra' => [
                        ['key' => 'assign', 'label' => '菜单授权', 'perm' => 'role:assign_menu'],
                    ],
                ],
            ],
        ]);
    }
}
