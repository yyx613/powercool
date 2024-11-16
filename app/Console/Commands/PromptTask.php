<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Models\UserTask;
use App\Notifications\MobileAppNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class PromptTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prompt-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to app';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date_to_notify = now()->addDays(3);

        $tasks = Task::where('prompted', false)->where('start_date', '<=', $date_to_notify)->get();

        for ($i=0; $i < count($tasks); $i++) { 
            $to_notify = UserTask::where('task_id', $tasks[$i]->id)->pluck('user_id')->toArray();

            Notification::send(User::whereIn('id', $to_notify)->get(), new MobileAppNotification([
                'type' => 'task_prompt',
                'task_id' => $tasks[$i]->id
            ]));

            $tasks[$i]->prompted = true;
            $tasks[$i]->save();
        }
    }
}
