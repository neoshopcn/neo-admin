<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recycle_bin_items', function (Blueprint $table) {
            $table->id();
            $table->string('source_table', 190)->comment('来源业务表名');
            $table->string('model_class', 255)->comment('删除时的模型类名，用于恢复');
            $table->json('payload')->comment('行快照 JSON');
            $table->timestamp('recycled_at')->comment('进入回收站时间');
            $table->foreignId('operator_id')->nullable()->comment('操作人')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recycle_bin_items');
    }
};
