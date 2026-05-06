<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Miniprogram extends Model
{
    protected $appends = [
        'check_status_label',
        'status_label',
    ];

    protected $fillable = [
        'name',
        'app_code',
        'app_id',
        'app_secret',
        'token',
        'aes_key',
        'logo',
        'check_status',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'check_status' => 'integer',
            'status' => 'integer',
        ];
    }

    protected function checkStatusLabel(): Attribute
    {
        return Attribute::get(function (): string {
            return match ((int) $this->check_status) {
                1 => '已通过',
                2 => '已拒绝',
                default => '待审核',
            };
        });
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn (): string => (int) $this->status === 1 ? '启用' : '禁用');
    }
}
