<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('app.env') == 'local') {
            for ($i=0; $i < 5; $i++) {
                $cu = Customer::create([
                    'name' => 'Customer'. ($i + 1),
                    'phone' => fake()->phoneNumber(),
                    'under_warranty' => fake()->boolean(),
                    'is_active' => true,
                ]);

                Branch::create([
                    'object_type' => Customer::class,
                    'object_id' => $cu->id,
                    'location' => fake()->randomElement([Branch::LOCATION_KL, Branch::LOCATION_PENANG]),
                ]);
            }
        }
    }
}
