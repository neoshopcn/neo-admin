<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('miniprogram_users', function (Blueprint $table) {
            $table->id();
            $table->string('openid', 64)->nullable()->comment('openid');
            $table->string('unionid', 64)->nullable()->comment('unionid');
            $table->string('app_code', 32)->default('')->comment('小程序标识');
            $table->string('nick_name', 255)->nullable()->comment('昵称');
            $table->string('city', 255)->nullable()->comment('市');
            $table->string('province', 255)->nullable()->comment('省');
            $table->string('country', 255)->nullable()->comment('区');
            $table->string('gender', 255)->default('0')->comment('性别');
            $table->string('avatar_url', 255)->nullable()->comment('头像');
            $table->string('phone', 20)->nullable()->comment('手机号');
            $table->string('member_name', 255)->nullable()->comment('姓名');
            $table->string('remark', 255)->nullable()->comment('备注');
            $table->string('tag', 255)->nullable()->comment('标签');
            $table->integer('m_level')->default(0)->comment('会员等级');
            $table->double('m_balance', 8, 2)->default(0)->comment('余额（Balance）');
            $table->double('m_points', 8, 2)->default(0)->comment('积分（Points）');
            $table->integer('c_views')->default(0)->comment('浏览次数');
            $table->integer('c_violation')->default(0)->comment('违规次数');
            $table->string('ip', 255)->nullable()->comment('用户IP');
            $table->enum('is_disabled', ['false', 'true'])->default('false')->comment('是否禁用');
            $table->enum('is_manager', ['false', 'true'])->default('false')->comment('是否管理员');
            $table->timestamps();

            $table->index('app_code');
            $table->index('openid');
            $table->index('unionid');
            $table->index('is_disabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miniprogram_users');
    }
};
