<?php

namespace Database\Seeders;

use App\Models\Branch;
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
            for ($i=0; $i < 5; $i++) {
                $su = Supplier::create([
                    'name' => 'Supplier'. ($i + 1),
                    'phone' => fake()->phoneNumber(),
                    // 'under_warranty' => fake()->boolean(),
                    'is_active' => true,
                ]);

                Branch::create([
                    'object_type' => Supplier::class,
                    'object_id' => $su->id,
                    'location' => fake()->randomElement([Branch::LOCATION_KL, Branch::LOCATION_PENANG]),
                ]);
            }
        }
    }
}
