<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        $pairs = DB::table('users')->whereNotNull('role_id')->get(['id', 'role_id']);
        foreach ($pairs as $row) {
            DB::table('role_user')->insert([
                'user_id' => $row->id,
                'role_id' => $row->role_id,
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('password')->constrained('roles')->nullOnDelete();
        });

        $rows = DB::table('role_user')
            ->selectRaw('user_id, min(role_id) as role_id')
            ->groupBy('user_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('users')->where('id', $row->user_id)->update(['role_id' => $row->role_id]);
        }

        Schema::dropIfExists('role_user');
    }
};
