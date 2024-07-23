<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'ad@gmail.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Super Admin');

        if (config('app.env') == 'local') {
            // Create 3 drivers
            for ($i=0; $i < 3; $i++) { 
                $user = User::create([
                    'name' => 'Driver'. ($i + 1),
                    'email' => 'dr' . ($i + 1) . '@gmail.com',
                    'password' => Hash::make('password'),
                ]);
                $user->assignRole('Driver');
            }
            // Create 3 sales
            for ($i=0; $i < 3; $i++) {
                $user = User::create([
                    'name' => 'Sale'. ($i + 1),
                    'email' => 'sa' . ($i + 1) . '@gmail.com',
                    'password' => Hash::make('password'),
                ]);
                $user->assignRole('Sale');
            }
            for ($i=0; $i < 3; $i++) {
                // Create 3 technician
                $user = User::create([
                    'name' => 'Technician'. ($i + 1),
                    'email' => 'te' . ($i + 1) . '@gmail.com',
                    'password' => Hash::make('password'),
                ]);
                $user->assignRole('Technician');
            }
        }
    }
}
