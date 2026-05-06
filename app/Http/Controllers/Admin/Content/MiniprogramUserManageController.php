<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Miniprogram;
use Illuminate\View\View;

/** 小程序用户管理（查询、编辑） */
class MiniprogramUserManageController extends Controller
{
    public function __invoke(): View
    {
        $appCodeOptions = Miniprogram::query()
            ->orderBy('id')
            ->get(['app_code', 'name'])
            ->map(fn (Miniprogram $m) => [
                'label' => $m->name.' ('.$m->app_code.')',
                'value' => $m->app_code,
            ])
            ->values()
            ->all();

        return view('admin.content.miniprogram-users', [
            'neo' => [
                'title' => '小程序用户',
                'listUrl' => url('/admin/api/miniprogram-users'),
                'uploadUrl' => url('/admin/api/upload'),
                'resourcesListUrl' => url('/admin/api/resources'),
                'storagePublicBase' => rtrim((string) config('filesystems.disks.public.url'), '/'),
                'rowKey' => 'id',
                'columns' => [
                    ['prop' => 'id', 'label' => 'ID', 'width' => 72],
                    ['prop' => 'avatar_url', 'label' => '头像', 'width' => 72, 'avatar' => true],
                    ['prop' => 'app_code', 'label' => '标识', 'minWidth' => 100],
                    ['prop' => 'openid', 'label' => 'OpenID', 'minWidth' => 220],
                    ['prop' => 'nick_name', 'label' => '昵称', 'minWidth' => 120],
                    ['prop' => 'phone', 'label' => '手机', 'width' => 120],
                    ['prop' => 'member_name', 'label' => '姓名', 'minWidth' => 100],
                    ['prop' => 'm_level', 'label' => '等级', 'width' => 72],
                    ['prop' => 'm_balance', 'label' => '余额', 'width' => 88],
                    ['prop' => 'm_points', 'label' => '积分', 'width' => 88],
                    ['prop' => 'is_disabled_label', 'label' => '禁用', 'width' => 80],
                    ['prop' => 'is_manager_label', 'label' => '管理员', 'width' => 88],
                    ['prop' => 'created_at', 'label' => '注册时间', 'minWidth' => 168],
                ],
                'filters' => [
                    ['type' => 'input', 'key' => 'keyword', 'placeholder' => 'OpenID / 昵称 / 手机 / 姓名 / 备注'],
                    [
                        'type' => 'select',
                        'key' => 'app_code',
                        'placeholder' => '小程序',
                        'clearable' => true,
                        'options' => $appCodeOptions,
                    ],
                    [
                        'type' => 'select',
                        'key' => 'is_disabled',
                        'placeholder' => '是否禁用',
                        'clearable' => true,
                        'options' => [
                            ['label' => '正常', 'value' => 'false'],
                            ['label' => '已禁用', 'value' => 'true'],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'key' => 'is_manager',
                        'placeholder' => '管理员',
                        'clearable' => true,
                        'options' => [
                            ['label' => '否', 'value' => 'false'],
                            ['label' => '是', 'value' => 'true'],
                        ],
                    ],
                    ['type' => 'daterange', 'startKey' => 'created_from', 'endKey' => 'created_to'],
                ],
                'formFields' => [
                    ['prop' => 'nick_name', 'label' => '昵称'],
                    ['prop' => 'member_name', 'label' => '姓名'],
                    ['prop' => 'phone', 'label' => '手机号'],
                    ['prop' => 'gender', 'label' => '性别'],
                    ['prop' => 'remark', 'label' => '备注'],
                    ['prop' => 'tag', 'label' => '标签'],
                    ['prop' => 'm_level', 'label' => '会员等级', 'placeholder' => '整数'],
                    ['prop' => 'm_balance', 'label' => '余额', 'placeholder' => '数字'],
                    ['prop' => 'm_points', 'label' => '积分', 'placeholder' => '数字'],
                    [
                        'prop' => 'is_disabled',
                        'label' => '禁用',
                        'input' => 'select',
                        'options' => [
                            ['label' => '正常', 'value' => 'false'],
                            ['label' => '已禁用', 'value' => 'true'],
                        ],
                        'rule' => ['required' => true],
                    ],
                    [
                        'prop' => 'is_manager',
                        'label' => '管理员',
                        'input' => 'select',
                        'options' => [
                            ['label' => '否', 'value' => 'false'],
                            ['label' => '是', 'value' => 'true'],
                        ],
                        'rule' => ['required' => true],
                    ],
                ],
                'perms' => [
                    'edit' => 'miniprogram_user:edit',
                    'view' => 'miniprogram_user:view',
                ],
            ],
        ]);
    }
}
