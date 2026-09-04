<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('job_title', 100)->nullable()->after('email');
            $table->string('phone', 30)->nullable()->after('job_title');
            $table->text('bio')->nullable()->after('phone');
            $table->string('avatar_disk', 40)->nullable()->after('bio');
            $table->string('avatar_path')->nullable()->after('avatar_disk');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['job_title', 'phone', 'bio', 'avatar_disk', 'avatar_path']);
        });
    }
};
