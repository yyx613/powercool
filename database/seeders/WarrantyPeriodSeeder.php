<?php

namespace Database\Seeders;

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
            $data = [];

            for ($i=0; $i < 5; $i++) { 
                $data[] = [
                    'name' => 'WP ' . fake()->randomNumber(),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            WarrantyPeriod::insert($data);
        }
    }
}
