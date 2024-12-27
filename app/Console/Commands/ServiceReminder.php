<?php

namespace App\Console\Commands;

use App\Models\InventoryServiceReminder;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ServiceReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ServiceReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:service-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind users N days before next service date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $receivers = User::whereHas('roles.permissions', function($q) {
                $q->whereIn('name', ['service_reminder.receive_reminder']);
            })->get();

        $srs = InventoryServiceReminder::whereNotNull('reminding_days')->whereNull('reminded_at')->get();

        for ($i=0; $i < count($srs); $i++) { 
            if (now()->format('Y-m-d') > Carbon::parse($srs[$i]->next_service_date)->subDays($srs[$i]->reminding_days)->format('Y-m-d')) {
                Notification::send($receivers, new ServiceReminderNotification([
                    'service_reminder' => $srs[$i]->id,
                    'desc' => 'The service date for ' . $srs[$i]->objectable->sku . ' is ' . Carbon::parse($srs[$i]->next_service_date)->format('d M Y'),
                ]));
    
                $srs[$i]->reminded_at = now();
                $srs[$i]->save();
            }
        }
    }
}
