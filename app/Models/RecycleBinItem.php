<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** 回收站条目 */
class RecycleBinItem extends Model
{
    public $timestamps = false;

    protected $table = 'recycle_bin_items';

    protected $fillable = [
        'source_table',
        'model_class',
        'payload',
        'recycled_at',
        'operator_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'recycled_at' => 'datetime',
            'operator_id' => 'integer',
        ];
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
