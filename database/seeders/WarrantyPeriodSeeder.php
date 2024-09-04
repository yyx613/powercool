<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\WarrantyPeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarrantyPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('app.env') == 'local') {
            $now = now();

            for ($i=0; $i < 5; $i++) { 
                $wp = WarrantyPeriod::create([
                    'name' => 'WP ' . fake()->randomNumber(),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);

                Branch::create([
                    'object_type' => WarrantyPeriod::class,
                    'object_id' => $wp->id,
                    'location' => fake()->randomElement([Branch::LOCATION_KL, Branch::LOCATION_PENANG]),
                ]);
            }
        }
    }
}
