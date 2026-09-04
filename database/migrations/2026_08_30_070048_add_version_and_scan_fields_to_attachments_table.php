<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table): void {
            $table->foreignId('root_attachment_id')->nullable()->after('id')->constrained('attachments')->nullOnDelete();
            $table->unsignedInteger('version')->default(1)->after('size');
            $table->boolean('is_current')->default(true)->after('version');
            $table->string('scan_status', 20)->default('unscanned')->after('is_current');
            $table->timestamp('scanned_at')->nullable()->after('scan_status');

            $table->index(['root_attachment_id', 'version']);
            $table->index(['attachable_type', 'attachable_id', 'is_current'], 'attachments_current_index');
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table): void {
            $table->dropIndex('attachments_current_index');
            $table->dropIndex(['root_attachment_id', 'version']);
            $table->dropConstrainedForeignId('root_attachment_id');
            $table->dropColumn(['version', 'is_current', 'scan_status', 'scanned_at']);
        });
    }
};
