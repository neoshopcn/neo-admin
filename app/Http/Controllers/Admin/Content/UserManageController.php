<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\View\View;

/** 用户管理页 */
class UserManageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.content.users', [
            'neo' => [
                'title' => '管理员用户',
                'listUrl' => url('/admin/api/users'),
                'uploadUrl' => url('/admin/api/upload'),
                'resourcesListUrl' => url('/admin/api/resources'),
                'storagePublicBase' => rtrim((string) config('filesystems.disks.public.url'), '/'),
                'rowKey' => 'id',
                'columns' => [
                    ['prop' => 'id', 'label' => 'ID', 'width' => 72],
                    ['prop' => 'avatar_url', 'label' => '头像', 'width' => 88, 'avatar' => true],
                    ['prop' => 'username', 'label' => '账号', 'minWidth' => 120],
                    ['prop' => 'name', 'label' => '姓名', 'minWidth' => 120],
                    ['prop' => 'email', 'label' => '邮箱', 'minWidth' => 160],
                    ['prop' => 'role_names', 'label' => '角色', 'minWidth' => 160],
                    ['prop' => 'status', 'label' => '状态', 'width' => 88, 'tag' => true],
                    ['prop' => 'created_at', 'label' => '创建时间', 'minWidth' => 168],
                ],
                'filters' => [
                    ['type' => 'input', 'key' => 'keyword', 'placeholder' => '账号 / 姓名 / 邮箱'],
                    [
                        'type' => 'select',
                        'key' => 'status',
                        'placeholder' => '状态',
                        'clearable' => true,
                        'options' => [['label' => '启用', 'value' => 1], ['label' => '禁用', 'value' => 0]],
                    ],
                    ['type' => 'daterange', 'startKey' => 'created_from', 'endKey' => 'created_to'],
                    [
                        'type' => 'select',
                        'key' => 'role_id',
                        'placeholder' => '角色',
                        'clearable' => true,
                        'options' => Role::query()->where('status', 1)->orderBy('id')->get(['id', 'name'])
                            ->map(fn (Role $r) => ['label' => $r->name, 'value' => $r->id])
                            ->all(),
                    ],
                ],
                'formFields' => [
                    ['prop' => 'username', 'label' => '账号', 'rule' => ['required' => true]],
                    ['prop' => 'name', 'label' => '姓名', 'rule' => ['required' => true]],
                    [
                        'prop' => 'avatar',
                        'label' => '头像',
                        'input' => 'upload',
                        'uploadScene' => 'avatar',
                        'uploadAccept' => 'image/jpeg,image/png,image/gif,image/webp',
                        'placeholder' => '从资源库选择或上传',
                    ],
                    ['prop' => 'email', 'label' => '邮箱', 'input' => 'email'],
                    [
                        'prop' => 'role_ids',
                        'label' => '角色',
                        'input' => 'select',
                        'multiple' => true,
                        'optionsUrl' => url('/admin/api/roles/options'),
                    ],
                    ['prop' => 'password', 'label' => '密码', 'input' => 'password', 'createOnly' => true],
                    ['prop' => 'password', 'label' => '新密码(留空不改)', 'input' => 'password', 'editOnly' => true],
                    [
                        'prop' => 'status',
                        'label' => '状态',
                        'input' => 'select',
                        'options' => [['label' => '启用', 'value' => 1], ['label' => '禁用', 'value' => 0]],
                        'rule' => ['required' => true],
                    ],
                ],
                'perms' => [
                    'create' => 'user:create',
                    'edit' => 'user:edit',
                    'delete' => 'user:delete',
                    'view' => 'user:view',
                    'extra' => [
                        ['key' => 'resetPwd', 'label' => '重置密码', 'perm' => 'user:reset_password'],
                    ],
                ],
            ],
        ]);
    }
}
