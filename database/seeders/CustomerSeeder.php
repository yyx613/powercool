<?php

namespace Database\Seeders;

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
        // Create 3 customers
        for ($i=0; $i < 3; $i++) {
            Customer::create([
                'name' => 'Customer'. ($i + 1),
                'phone' => fake()->phoneNumber(),
                'under_warranty' => fake()->boolean(),
                'is_active' => fake()->boolean(),
            ]);
        }
    }
}
