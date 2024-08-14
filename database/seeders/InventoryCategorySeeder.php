<?php

namespace Database\Seeders;

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
            $data = [];

            for ($i=0; $i < 5; $i++) { 
                $data[] = [
                    'name' => 'Cat ' . fake()->randomNumber(),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            InventoryCategory::insert($data);
        }
    }
}
