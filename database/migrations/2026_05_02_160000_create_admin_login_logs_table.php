<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('username', 64);
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('success')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at', 'success']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_logs');
    }
};
