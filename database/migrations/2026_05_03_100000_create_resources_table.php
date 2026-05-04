<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('original_name', 255)->comment('原始文件名');
            $table->string('storage_path', 512)->comment('存储相对路径');
            $table->string('mime_type', 128)->nullable()->comment('MIME');
            $table->string('extension', 32)->nullable()->comment('扩展名');
            $table->unsignedBigInteger('size_bytes')->default(0)->comment('字节大小');
            $table->string('scene', 32)->index()->comment('上传场景');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('上传人');
            $table->string('disk', 32)->comment('存储磁盘名');
            $table->unsignedTinyInteger('status')->default(1)->index()->comment('1正常 0停用');
            $table->text('tags')->nullable()->comment('JSON 数组标签');
            $table->timestamp('uploaded_at')->useCurrent()->comment('文件上传时间');
            $table->timestamps();

            $table->index(['scene', 'status']);
            $table->index('uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
