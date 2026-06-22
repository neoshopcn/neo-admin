<?php

namespace App\Models;

use App\Models\Concerns\SerializesDisplayDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigItem extends Model
{
    use SerializesDisplayDates;

    protected $fillable = [
        'group_id',
        'section_id',
        'name',
        'label',
        'type',
        'value',
        'default',
        'options',
        'rules',
        'required',
        'sort',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'group_id' => 'integer',
            'section_id' => 'integer',
            'options' => 'array',
            'required' => 'integer',
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(ConfigSection::class, 'section_id');
    }
}
