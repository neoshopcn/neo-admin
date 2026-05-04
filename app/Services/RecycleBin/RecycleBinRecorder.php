<?php

namespace App\Services\RecycleBin;

use App\Models\RecycleBinItem;
use Illuminate\Database\Eloquent\Model;

/** 将删除快照写入 recycle_bin_items */
class RecycleBinRecorder
{
    public function record(Model $model): void
    {
        $payload = $model->getAttributes();

        RecycleBinItem::query()->create([
            'source_table' => $model->getTable(),
            'model_class' => $model::class,
            'payload' => $payload,
            'recycled_at' => now(),
            'operator_id' => auth()->id()??0,
        ]);
    }
}
