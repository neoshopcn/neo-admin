<?php

namespace App\Models;

use App\Models\Concerns\SerializesDisplayDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfigSection extends Model
{
    use SerializesDisplayDates;

    protected $fillable = [
        'group_id',
        'name',
        'label',
        'icon',
        'sort',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'group_id' => 'integer',
            'sort' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ConfigGroup::class, 'group_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConfigItem::class, 'section_id');
    }
}
