<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_groups', function (Blueprint $table) {
            $table->id();
            $table->string('page', 32)->comment('所属页面：system 系统配置 / api 接口配置');
            $table->string('name', 64)->comment('英文标识');
            $table->string('label', 128)->comment('中文显示');
            $table->string('icon', 64)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('1启用 0禁用');
            $table->timestamps();

            $table->unique(['page', 'name']);
            $table->index(['page', 'status', 'sort']);
        });

        Schema::create('config_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('config_groups')->cascadeOnDelete();
            $table->string('name', 64)->comment('英文标识');
            $table->string('label', 128)->comment('中文显示');
            $table->string('icon', 64)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('1启用 0禁用');
            $table->timestamps();

            $table->unique(['group_id', 'name']);
            $table->index(['group_id', 'status', 'sort']);
        });

        Schema::create('config_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('config_groups')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('config_sections')->cascadeOnDelete();
            $table->string('name', 128)->comment('配置 key');
            $table->string('label', 128)->comment('显示名称');
            $table->string('type', 32)->default('text')->comment('text/password/switch/select/number/json');
            $table->text('value')->nullable();
            $table->text('default')->nullable();
            $table->json('options')->nullable()->comment('select 等类型的选项');
            $table->string('rules', 512)->nullable()->comment('Laravel 校验规则片段');
            $table->unsignedTinyInteger('required')->default(0)->comment('1必填 0非必填');
            $table->unsignedInteger('sort')->default(0);
            $table->unsignedTinyInteger('status')->default(1)->comment('1启用 0禁用');
            $table->timestamps();

            $table->unique(['section_id', 'name']);
            $table->index(['group_id', 'section_id', 'status', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_items');
        Schema::dropIfExists('config_sections');
        Schema::dropIfExists('config_groups');
    }
};
