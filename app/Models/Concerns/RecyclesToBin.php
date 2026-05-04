<?php

namespace App\Models\Concerns;

use App\Services\RecycleBin\RecycleBinRecorder;
use Illuminate\Database\Eloquent\Model;

/** 删除前写入 recycle_bin_items。详见 docs/recycle-bin.md */
trait RecyclesToBin
{
    /** 为 true 时跳过回收站 */
    protected bool $recycleBinBypass = false;

    public static function bootRecyclesToBin(): void
    {
        static::deleting(function (Model $model) {
            if (! $model instanceof static) {
                return;
            }

            if ($model->recycleBinBypass) {
                return;
            }

            if (! static::recycleBinEnabled()) {
                return;
            }

            app(RecycleBinRecorder::class)->record($model);
        });
    }

    /**
     * 是否启用回收站
     */
    protected static function recycleBinEnabled(): bool
    {
        return true;
    }

    /**
     * 恢复前调整快照
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function recycleBinHydratePayload(array $payload): array
    {
        return $payload;
    }

    /**
     * 为 true 时用 DB::insert 恢复，绕过 cast/mutator
     */
    public static function recycleBinUsesRawInsert(): bool
    {
        return false;
    }

    /**
     * 跳过回收站直接删除业务行
     */
    public function deleteWithoutRecycleBin(): ?bool
    {
        $this->recycleBinBypass = true;

        try {
            return parent::delete();
        } finally {
            $this->recycleBinBypass = false;
        }
    }
}
