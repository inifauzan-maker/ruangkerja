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
        Schema::table('users', function (Blueprint $table) {
            $table->string('global_role', 30)->default('user');
            $table->boolean('is_active')->default(true);
            $table->index(['global_role', 'is_active'], 'users_global_role_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_global_role_active_idx');
            $table->dropColumn(['global_role', 'is_active']);
        });
    }
};
