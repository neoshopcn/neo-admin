<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default(0)->index();
            $table->string('name', 128);
            $table->string('icon', 64)->nullable()->comment('Element Plus icon 名或 class');
            $table->string('path', 255)->nullable()->comment('iframe 内容地址或路由标识');
            $table->unsignedInteger('sort')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('1启用 0禁用');
            $table->unsignedTinyInteger('type')->default(0)->comment('0目录 1菜单 2按钮');
            $table->string('permission_code', 128)->nullable()->index()->comment('后端与按钮鉴权标识');
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
