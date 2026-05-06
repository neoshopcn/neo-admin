<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MiniprogramUser extends Model
{
    protected $table = 'miniprogram_users';

    protected $appends = [
        'is_disabled_label',
        'is_manager_label',
    ];

    protected $fillable = [
        'openid',
        'unionid',
        'app_code',
        'nick_name',
        'city',
        'province',
        'country',
        'gender',
        'avatar_url',
        'phone',
        'member_name',
        'remark',
        'tag',
        'm_level',
        'm_balance',
        'm_points',
        'c_views',
        'c_violation',
        'ip',
        'is_disabled',
        'is_manager',
    ];

    protected function casts(): array
    {
        return [
            'm_level' => 'integer',
            'm_balance' => 'float',
            'm_points' => 'float',
            'c_views' => 'integer',
            'c_violation' => 'integer',
        ];
    }

    public function miniprogram(): BelongsTo
    {
        return $this->belongsTo(Miniprogram::class, 'app_code', 'app_code');
    }

    protected function isDisabledLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->is_disabled === 'true' ? '已禁用' : '正常');
    }

    protected function isManagerLabel(): Attribute
    {
        return Attribute::get(fn (): string => $this->is_manager === 'true' ? '是' : '否');
    }
}
