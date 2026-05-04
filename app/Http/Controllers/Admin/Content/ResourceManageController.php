<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** 资源管理页 */
class ResourceManageController extends Controller
{
    public function __invoke(): View
    {
        $sceneOptions = collect(config('upload.scenes', []))
            ->keys()
            ->map(fn (string $k) => ['label' => $k, 'value' => $k])
            ->values()
            ->all();

        return view('admin.content.resources', [
            'neo' => [
                'title' => '资源管理',
                'listUrl' => url('/admin/api/resources'),
                'uploadUrl' => url('/admin/api/upload'),
                'storagePublicBase' => rtrim((string) config('filesystems.disks.public.url'), '/'),
                'resourcesListUrl' => url('/admin/api/resources'),
                'rowKey' => 'id',
                'columns' => [
                    ['prop' => 'id', 'label' => 'ID', 'width' => 72],
                    ['prop' => 'original_name', 'label' => '文件名', 'minWidth' => 160],
                    ['prop' => 'storage_path', 'label' => '路径', 'minWidth' => 200],
                    ['prop' => 'scene', 'label' => '场景', 'width' => 100],
                    ['prop' => 'extension', 'label' => '类型', 'width' => 72],
                    ['prop' => 'size_label', 'label' => '大小', 'width' => 88],
                    ['prop' => 'disk', 'label' => '磁盘', 'width' => 88],
                    ['prop' => 'uploader_name', 'label' => '上传人', 'minWidth' => 120],
                    ['prop' => 'uploaded_at', 'label' => '上传时间', 'minWidth' => 168],
                    ['prop' => 'tags_display', 'label' => '标签', 'minWidth' => 120],
                    ['prop' => 'status', 'label' => '状态', 'width' => 88, 'tag' => true, 'statusLabels' => ['正常', '停用']],
                ],
                'filters' => [
                    ['type' => 'input', 'key' => 'keyword', 'placeholder' => '文件名 / 路径'],
                    [
                        'type' => 'select',
                        'key' => 'scene',
                        'placeholder' => '场景',
                        'clearable' => true,
                        'options' => $sceneOptions,
                    ],
                    [
                        'type' => 'select',
                        'key' => 'status',
                        'placeholder' => '状态',
                        'clearable' => true,
                        'options' => [['label' => '正常', 'value' => 1], ['label' => '停用', 'value' => 0]],
                    ],
                    ['type' => 'input', 'key' => 'file_type', 'placeholder' => '扩展名 / MIME 片段'],
                    ['type' => 'daterange', 'startKey' => 'uploaded_from', 'endKey' => 'uploaded_to'],
                ],
                'formFields' => [
                    [
                        'prop' => 'scene',
                        'label' => '场景',
                        'input' => 'select',
                        'options' => $sceneOptions,
                        'rule' => ['required' => true],
                        'createOnly' => true,
                    ],
                    [
                        'prop' => 'tags',
                        'label' => '标签',
                        'input' => 'text',
                        'placeholder' => '可选，逗号分隔',
                    ],
                    [
                        'prop' => 'storage_path',
                        'label' => '文件',
                        'input' => 'upload',
                        'createOnly' => true,
                        'pickFromLibrary' => false,
                        'uploadSceneFromForm' => 'scene',
                        'uploadTagsFromForm' => 'tags',
                        'uploadAccept' => '*/*',
                        'uploadScene' => 'avatar',
                        'placeholder' => '上传文件至资源库',
                    ],
                    [
                        'prop' => 'status',
                        'label' => '状态',
                        'input' => 'select',
                        'options' => [['label' => '正常', 'value' => 1], ['label' => '停用', 'value' => 0]],
                        'rule' => ['required' => true],
                        'editOnly' => true,
                    ],
                ],
                'perms' => [
                    'create' => 'resource:create',
                    'edit' => 'resource:edit',
                    'delete' => 'resource:delete',
                    'view' => 'resource:view',
                ],
            ],
        ]);
    }
}
