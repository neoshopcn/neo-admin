<?php

return [

    'disk' => env('UPLOAD_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | 上传场景
    |--------------------------------------------------------------------------
    |
    | directory：相对磁盘根目录，不以 / 开头
    | max_kb：验证规则 max，单位 KB
    | mimes：允许的扩展类型
    |
    */
    'scenes' => [
        'avatar' => [
            'directory' => 'uploads/avatars',
            'max_kb' => (int) env('UPLOAD_AVATAR_MAX_KB', 2048),
            'mimes' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
        ],
        'document' => [
            'directory' => 'uploads/documents',
            'max_kb' => (int) env('UPLOAD_DOCUMENT_MAX_KB', 5120),
            'mimes' => ['pdf', 'doc', 'docx', 'xlsx', 'zip', 'png', 'jpeg', 'jpg', 'gif', 'webp', 'txt'],
        ],
        'richtext' => [
            'directory' => 'uploads/richtext',
            'max_kb' => (int) env('UPLOAD_RICHTEXT_MAX_KB', 3072),
            'mimes' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
        ],
    ],

];
