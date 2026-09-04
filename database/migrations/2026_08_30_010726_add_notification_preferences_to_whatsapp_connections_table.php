<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_connections', function (Blueprint $table) {
            $table->boolean('notify_due_reminders')->default(true)->after('notify_announcements');
            $table->boolean('quiet_hours_enabled')->default(true)->after('notify_due_reminders');
            $table->string('timezone', 64)->default('Asia/Jakarta')->after('quiet_hours_enabled');
            $table->time('quiet_hours_start')->default('21:00')->after('timezone');
            $table->time('quiet_hours_end')->default('07:00')->after('quiet_hours_start');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_connections', function (Blueprint $table) {
            $table->dropColumn([
                'notify_due_reminders',
                'quiet_hours_enabled',
                'timezone',
                'quiet_hours_start',
                'quiet_hours_end',
            ]);
        });
    }
};
