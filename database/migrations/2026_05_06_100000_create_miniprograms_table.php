<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('miniprograms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->comment('小程序名称');
            $table->string('app_code', 32)->comment('小程序标识');
            $table->string('app_id', 64)->default('')->comment('小程序ID');
            $table->string('app_secret', 128)->default('')->comment('小程序密钥');
            $table->string('token', 200)->nullable()->comment('Token');
            $table->string('aes_key', 200)->nullable()->comment('AES Key');
            $table->string('logo', 512)->nullable()->comment('小程序二维码图片路径');
            $table->unsignedTinyInteger('check_status')->default(0)->comment('审核状态 0待审核 1已通过 2已拒绝');
            $table->unsignedTinyInteger('status')->default(1)->comment('1启用 0禁用');
            $table->timestamps();

            $table->unique('app_code');
            $table->index('status');
            $table->index('check_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miniprograms');
    }
};
