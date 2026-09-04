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
        Schema::create('whatsapp_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('phone_number_id', 30);
            $table->text('access_token');
            $table->string('recipient_phone', 20);
            $table->string('template_name', 100)->default('timmanager_notification');
            $table->string('template_language', 10)->default('id');
            $table->boolean('is_active')->default(true);
            $table->boolean('notify_task_created')->default(true);
            $table->boolean('notify_task_updated')->default(true);
            $table->boolean('notify_chat_messages')->default(false);
            $table->boolean('notify_announcements')->default(true);
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->string('last_message_id')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_connections');
    }
};
