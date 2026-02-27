<?php
namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTaskReminders extends Command
{
    protected $signature = 'crm:send-reminders';
    protected $description = 'Send task reminder notifications';

    public function handle(): void
    {
        $tasks = Task::with(['user', 'customer', 'assignedUser'])
            ->where('status', 'pending')
            ->where('reminder_sent', false)
            ->where('reminder_at', '<=', now())
            ->get();

        foreach ($tasks as $task) {
            $notifyUser = $task->assignedUser ?? $task->user;
            if ($notifyUser && $notifyUser->email) {
                // In a real app, use Laravel Notifications
                $this->info("Reminder sent for task: {$task->title} to {$notifyUser->email}");
                $task->update(['reminder_sent' => true]);
            }
        }

        $this->info("Processed {$tasks->count()} reminders.");
    }
}
