<?php

namespace App\Console\Commands;

use App\Models\Scopes\BranchScope;
use App\Models\User;
use App\Models\VehicleService;
use App\Notifications\VehicleServiceNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class VehicleServiceReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:vehicle-service-reminder';

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
        $receivers = User::withoutGlobalScope(BranchScope::class)->whereHas('roles.permissions', function ($q) {
            $q->whereIn('name', ['vehicle_service.reminder']);
        })->get();

        $now = now()->format('Y-m-d');

        $data = VehicleService::orWhere('insurance_remind_at', 'like', '%'.$now.'%')
            ->orWhere('roadtax_remind_at', 'like', '%'.$now.'%')
            ->orWhere('inspection_remind_at', 'like', '%'.$now.'%')
            ->orWhere('mileage_remind_at', 'like', '%'.$now.'%')
            ->get();

        for ($i = 0; $i < count($data); $i++) {
            Notification::send($receivers, new VehicleServiceNotification([
                'type' => 'vehicle_service',
                'vehicle_service_id' => $data[$i]->id,
                'desc' => 'The service date for vehicle ('.$data[$i]->vehicle->plate_number.') is '.now()->format('d M Y'),
            ]));
        }
    }
}
