<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            'YOGES' => 'YOGES4848',
            'JINNIE' => 'JINNIE4848',
            'JENNY' => 'JENNY4848',
            'AGNES' => 'AGNES123',
            'ZACKVIE' => 'ZACKVIE123',
            'HANG' => 'HANG123',
            'YEAN' => 'YEAN123',
            'AIDIL' => 'AIDIL5566',
            'WEIJIE' => 'WEIJIE888',
            'CAREY' => 'CAREY888',
            'JES' => 'JES888',
            'KEONG' => 'KEONG888',
            'MC' => 'MC888',
            'YING' => 'YING666',
            'FATIHAH' => 'FATIHAH666',
            'MIA' => 'MIA666',
            'BIPIN' => 'BIPIN666',
            'IZAN' => 'IZAN777',
            'WONG' => 'WONG777',
            'VIKNISH' => 'VIKNISH777',
            'ARIF' => 'ARIF777',
            'KENNY' => 'KENNY777',
            'HARRY' => 'HARRY777',
            'ANDREWSON' => 'ANDREWSON777',
            'HAIKAL' => 'HAIKAL777',
            'HANAFI' => 'HANAFI777',
            'JEEVA' => 'JEEVA777',
            'FITHRI' => 'FITHRI777',
            'SHABIL' => 'SHABIL999',
            'RAIHAH' => 'RAIHAH000',
            'AZIZI' => 'AZIZI000',
            'RAHIM' => 'RAHIM000',
            'MURUGAN' => 'MURUGAN000',
            'SHARIS' => 'SHARIS000',
            'MOHAMMAD' => 'MOHAMMAD000',
            'ZAIDI' => 'ZAIDI333',
            'NAZRI' => 'NAZRI333',
            'SIVAGANGGAH' => 'SIVAGANGGAH333',
            'MIN KO' => 'MINKO333',
            'KYAW YE TUN' => 'CHOKK333',
            'HLA MAUNG' => 'LAMO333',
            'SHAHABUL' => 'SHAHABUL333',
            'TUN' => 'TUN333',
            'KAUSAR' => 'KAUSAR333',
            'RESIDEV' => 'RESIDEV333',
            'AH TUN CHAY' => 'CHAY333',
            'YE TUN ZAW' => 'ZAW333',
            'NURUL' => 'NURUL333',
            'RIMON' => 'RIMON333',
            'RONI' => 'RONI333',
            'ASHA' => 'ASHA333',
            'NAYLI' => 'NAYLI333',
            'IBHAM' => 'IBHAM333',
            'SAN LIN' => 'SANLIN333',
        ];

        foreach ($users as $name => $password) {
            $user = User::withoutGlobalScopes()->where('name', $name)->first();

            if ($user) {
                $user->update(['password' => Hash::make($password)]);
                $this->command->info("Updated password for: {$name}");
            } else {
                $this->command->warn("User not found: {$name}");
            }
        }
    }
}
