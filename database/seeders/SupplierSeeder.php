<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (config('app.env') == 'local') {
            for ($i=0; $i < 3; $i++) {
                Supplier::create([
                    'name' => 'Supplier'. ($i + 1),
                    'phone' => fake()->phoneNumber(),
                    'under_warranty' => fake()->boolean(),
                    'is_active' => true,
                ]);
            }
        }
    }
}
