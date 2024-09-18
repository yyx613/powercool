<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

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
            'sku' => (new User)->generateSku(),
        ]);
        $user->assignRole('Super Admin');

        if (config('app.env') == 'local') {
            // Create 3 drivers
            for ($i = 0; $i < 3; $i++) {
                $user = User::create([
                    'name' => 'Driver' . ($i + 1),
                    'email' => 'dr' . ($i + 1) . '@gmail.com',
                    'password' => Hash::make('password'),
                    'sku' => (new User)->generateSku(),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'address' => fake()->address(),
                    'phone_number' => fake()->phoneNumber(),
                    'is_active' => true,
                ]);
                $user->assignRole('Driver');

                Branch::create([
                    'object_type' => User::class,
                    'object_id' => $user->id,
                    'location' => fake()->randomElement([Branch::LOCATION_KL, Branch::LOCATION_PENANG]),
                ]);
            }
            // Create 3 sales
            for ($i = 0; $i < 3; $i++) {
                $user = User::create([
                    'name' => 'Sale' . ($i + 1),
                    'email' => 'sa' . ($i + 1) . '@gmail.com',
                    'password' => Hash::make('password'),
                    'sku' => (new User)->generateSku(),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'address' => fake()->address(),
                    'phone_number' => fake()->phoneNumber(),
                    'is_active' => true,
                ]);
                $user->assignRole('Sale');

                Branch::create([
                    'object_type' => User::class,
                    'object_id' => $user->id,
                    'location' => fake()->randomElement([Branch::LOCATION_KL, Branch::LOCATION_PENANG]),
                ]);
            }
            for ($i = 0; $i < 3; $i++) {
                // Create 3 technician
                $user = User::create([
                    'name' => 'Technician' . ($i + 1),
                    'email' => 'te' . ($i + 1) . '@gmail.com',
                    'password' => Hash::make('password'),
                    'sku' => (new User)->generateSku(),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'address' => fake()->address(),
                    'phone_number' => fake()->phoneNumber(),
                    'is_active' => true,
                ]);
                $user->assignRole('Technician');

                Branch::create([
                    'object_type' => User::class,
                    'object_id' => $user->id,
                    'location' => fake()->randomElement([Branch::LOCATION_KL, Branch::LOCATION_PENANG]),
                ]);
            }
        }
    }
}
