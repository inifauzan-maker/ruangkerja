<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_connection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('idempotency_key', 191)->unique();
            $table->string('event', 50);
            $table->string('event_label', 100);
            $table->string('subject');
            $table->string('project_name');
            $table->text('url');
            $table->string('status', 20)->default('pending');
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->string('message_id')->nullable();
            $table->timestamp('error_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['whatsapp_connection_id', 'created_at'], 'wa_logs_connection_created_idx');
            $table->index(['status', 'scheduled_for'], 'wa_logs_status_scheduled_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_logs');
    }
};
