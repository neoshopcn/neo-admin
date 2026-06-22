<?php

namespace App\Models;

use App\Models\Concerns\SerializesDisplayDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigGroup extends Model
{
    use SerializesDisplayDates;

    public const PAGE_SYSTEM = 'system';

    public const PAGE_API = 'api';

    protected $fillable = [
        'page',
        'name',
        'label',
        'icon',
        'sort',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ConfigSection::class, 'group_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConfigItem::class, 'group_id');
    }
}
