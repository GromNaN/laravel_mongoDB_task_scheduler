<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskReminderNotification;
use Illuminate\Support\Facades\Mail;


class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-task-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now('GMT+1');
        $upcomingTasks = Task::where('reminder_time', '<=', $now->toDateTimeString())
                                    ->where('reminder_time', '>=', $now->clone()->subMinutes(10)->toDateTimeString())
                                    ->get();

        foreach ($upcomingTasks as $task) {

            $emailBody = "
                Hello,
                This is a reminder for your task:
                Title: {$task->title}
                Description: {$task->description}
                Due Date: {$task->due_date}
                Please make sure to complete it on time!
                Regards,
                Your Task Reminder App
            ";

            Mail::raw($emailBody, function ($message) use ($task) {
                $message->to($task->email)
                        ->subject("Task Reminder: {$task->title}");
            });
            $this->info("Reminder email sent for task: {$task->title}");
        }

        return 0;
    }
}
