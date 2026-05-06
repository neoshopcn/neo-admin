<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** 小程序配置管理 */
class MiniprogramManageController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.content.miniprograms', [
            'neo' => [
                'title' => '小程序配置',
                'listUrl' => url('/admin/api/miniprograms'),
                'uploadUrl' => url('/admin/api/upload'),
                'resourcesListUrl' => url('/admin/api/resources'),
                'storagePublicBase' => rtrim((string) config('filesystems.disks.public.url'), '/'),
                'rowKey' => 'id',
                'columns' => [
                    ['prop' => 'id', 'label' => 'ID', 'width' => 72],
                    ['prop' => 'name', 'label' => '名称', 'minWidth' => 140],
                    ['prop' => 'app_code', 'label' => '标识', 'minWidth' => 120],
                    ['prop' => 'app_id', 'label' => 'AppID', 'minWidth' => 160],
                    ['prop' => 'check_status_label', 'label' => '审核', 'width' => 88],
                    ['prop' => 'status', 'label' => '状态', 'width' => 88, 'tag' => true, 'statusLabels' => ['启用', '禁用']],
                    ['prop' => 'created_at', 'label' => '创建时间', 'minWidth' => 168],
                    ['prop' => 'updated_at', 'label' => '更新时间', 'minWidth' => 168],
                ],
                'filters' => [
                    ['type' => 'input', 'key' => 'keyword', 'placeholder' => '名称 / 标识 / AppID'],
                    [
                        'type' => 'select',
                        'key' => 'check_status',
                        'placeholder' => '审核状态',
                        'clearable' => true,
                        'options' => [
                            ['label' => '待审核', 'value' => 0],
                            ['label' => '已通过', 'value' => 1],
                            ['label' => '已拒绝', 'value' => 2],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'key' => 'status',
                        'placeholder' => '启用状态',
                        'clearable' => true,
                        'options' => [['label' => '启用', 'value' => 1], ['label' => '禁用', 'value' => 0]],
                    ],
                    ['type' => 'daterange', 'startKey' => 'created_from', 'endKey' => 'created_to'],
                ],
                'formFields' => [
                    ['prop' => 'name', 'label' => '小程序名称', 'rule' => ['required' => true]],
                    ['prop' => 'app_code', 'label' => '小程序标识', 'rule' => ['required' => true]],
                    ['prop' => 'app_id', 'label' => '小程序 AppID', 'rule' => ['required' => true]],
                    ['prop' => 'app_secret', 'label' => 'AppSecret', 'placeholder' => '可留空'],
                    ['prop' => 'token', 'label' => 'Token', 'placeholder' => '可选'],
                    ['prop' => 'aes_key', 'label' => 'AES Key', 'placeholder' => '可选'],
                    [
                        'prop' => 'logo',
                        'label' => '二维码图',
                        'input' => 'upload',
                        'uploadScene' => 'miniprogram_logo',
                        'uploadAccept' => 'image/jpeg,image/png,image/gif,image/webp',
                        'placeholder' => '上传或填写 URL',
                    ],
                    [
                        'prop' => 'check_status',
                        'label' => '审核状态',
                        'input' => 'select',
                        'options' => [
                            ['label' => '待审核', 'value' => 0],
                            ['label' => '已通过', 'value' => 1],
                            ['label' => '已拒绝', 'value' => 2],
                        ],
                        'rule' => ['required' => true],
                    ],
                    [
                        'prop' => 'status',
                        'label' => '启用',
                        'input' => 'select',
                        'options' => [['label' => '启用', 'value' => 1], ['label' => '禁用', 'value' => 0]],
                        'rule' => ['required' => true],
                    ],
                ],
                'perms' => [
                    'create' => 'miniprogram:create',
                    'edit' => 'miniprogram:edit',
                    'delete' => 'miniprogram:delete',
                    'view' => 'miniprogram:view',
                ],
            ],
        ]);
    }
}
