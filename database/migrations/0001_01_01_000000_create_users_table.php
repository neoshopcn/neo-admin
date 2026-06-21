<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 64)->unique();
            $table->string('name');
            $table->string('avatar', 512)->nullable()->comment('头像 URL 或存储相对路径');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignId('role_id')->nullable();
            $table->unsignedTinyInteger('status')->default(1)->comment('1启用 0禁用');
            $table->string('google2fa_secret', 255)->nullable();
            $table->unsignedTinyInteger('google2fa_enabled')->default(0)->comment('1开启 0关闭');
            $table->timestamp('google2fa_locked_until')->nullable()->comment('2FA验证失败锁定截止时间');
            $table->unsignedBigInteger('google2fa_last_timeslice')->nullable()->comment('上次成功验证的 TOTP 时间片，防重放');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
