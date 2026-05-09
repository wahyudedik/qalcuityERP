<?php

namespace App\Console\Commands;

use App\Models\ErpNotification;
use App\Models\Reminder;
use App\Notifications\ReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class ProcessReminders extends Command
{
    protected $signature = 'reminders:process';

    protected $description = 'Kirim notifikasi untuk reminder yang sudah jatuh tempo';

    public function handle(): void
    {
        $due = Reminder::pending()->due()->with(['user', 'tenant'])->get();

        foreach ($due as $reminder) {
            // In-app notification
            ErpNotification::create([
                'tenant_id' => $reminder->tenant_id,
                'user_id' => $reminder->user_id,
                'type' => 'reminder',
                'title' => '⏰ '.$reminder->title,
                'body' => $reminder->notes ?? 'Pengingat Anda telah jatuh tempo.',
                'data' => ['reminder_id' => $reminder->id],
            ]);

            // Email channel
            if ($reminder->channel === 'email' && $reminder->user?->email) {
                try {
                    Notification::route('mail', $reminder->user->email)
                        ->notify(new ReminderNotification($reminder));
                } catch (\Throwable $e) {
                    $this->warn("Gagal kirim email reminder #{$reminder->id}: ".$e->getMessage());
                }
            }

            $reminder->update(['status' => 'sent']);
        }

        $this->info("Processed {$due->count()} reminders.");
    }
}
