<?php

namespace App\Services\RecycleBin;

use App\Models\Concerns\RecyclesToBin;
use App\Models\RecycleBinItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecycleBinRestorer
{
    /**
     * 从回收站恢复一行到业务表，并删除回收站记录。
     *
     * @throws ValidationException
     */
    public function restore(RecycleBinItem $item): Model
    {
        $class = $item->model_class;
        if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            throw ValidationException::withMessages([
                'model_class' => '无法恢复：模型类无效',
            ]);
        }

        $traits = class_uses_recursive($class);
        if (! in_array(RecyclesToBin::class, $traits, true)) {
            throw ValidationException::withMessages([
                'model_class' => '无法恢复：该模型未注册 RecyclesToBin trait',
            ]);
        }

        /** @var Model $instance */
        $instance = new $class;
        $payload = $class::recycleBinHydratePayload($item->payload);

        try {
            return DB::transaction(function () use ($class, $payload, $instance, $item) {
                if ($class::recycleBinUsesRawInsert()) {
                    $this->insertRaw($instance->getTable(), $payload);
                    $key = $instance->getKeyName();
                    $id = $payload[$key] ?? null;
                    if ($id === null) {
                        throw ValidationException::withMessages([
                            'payload' => '无法恢复：快照缺少主键 '.$key,
                        ]);
                    }

                    $model = $class::query()->findOrFail($id);
                } else {
                    $model = Model::unguarded(fn () => $class::query()->create($payload));
                }

                $item->delete();

                return $model;
            });
        } catch (QueryException $e) {
            throw ValidationException::withMessages([
                'payload' => '恢复失败：'.$e->getMessage(),
            ]);
        }
    }

    /** 删除回收站记录 */
    public function purge(RecycleBinItem $item): void
    {
        $item->delete();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function insertRaw(string $table, array $payload): void
    {
        DB::table($table)->insert($payload);
    }
}
