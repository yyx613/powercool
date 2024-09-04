<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\InventoryCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('app.env') == 'local') {
            $now = now();

            for ($i=0; $i < 5; $i++) { 
                $ic = InventoryCategory::create([
                    'name' => 'Cat ' . fake()->randomNumber(),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);

                Branch::create([
                    'object_type' => InventoryCategory::class,
                    'object_id' => $ic->id,
                    'location' => fake()->randomElement([Branch::LOCATION_KL, Branch::LOCATION_PENANG]),
                ]);
            }
        }
    }
}
