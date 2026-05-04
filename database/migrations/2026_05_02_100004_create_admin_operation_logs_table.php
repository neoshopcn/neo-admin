<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('username', 64)->nullable();
            $table->string('method', 16)->nullable();
            $table->string('path', 255)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('action', 128)->nullable();
            $table->text('payload')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_operation_logs');
    }
};
