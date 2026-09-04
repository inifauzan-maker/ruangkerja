<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_connections', function (Blueprint $table): void {
            $table->timestamp('consented_at')->nullable()->after('is_active');
            $table->timestamp('opted_out_at')->nullable()->after('consented_at');
        });

        DB::table('whatsapp_connections')
            ->where('is_active', true)
            ->whereNull('consented_at')
            ->update(['consented_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('whatsapp_connections', function (Blueprint $table): void {
            $table->dropColumn(['consented_at', 'opted_out_at']);
        });
    }
};
