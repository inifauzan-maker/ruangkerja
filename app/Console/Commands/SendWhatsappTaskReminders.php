<?php

namespace App\Console\Commands;

use App\Actions\DispatchWhatsappTaskReminders;
use Illuminate\Console\Command;

class SendWhatsappTaskReminders extends Command
{
    protected $signature = 'whatsapp:send-task-reminders';

    protected $description = 'Queue WhatsApp reminders for tasks due today or tomorrow';

    public function handle(DispatchWhatsappTaskReminders $dispatchReminders): int
    {
        $queued = $dispatchReminders->execute();

        $this->info("{$queued} pengingat WhatsApp dimasukkan ke antrean.");

        return self::SUCCESS;
    }
}
