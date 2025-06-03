<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\DebtorType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DebtorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];
        $types = ['AGENT', 'CUSTOMER', 'DEALER', 'SHOPEE', 'TIKTOK', 'WEBSITE'];

        for ($i = 0; $i < count($branches); $i++) {
            for ($j = 0; $j < count($types); $j++) {
                $dt = DebtorType::create([
                    'name' => $types[$j],
                    'is_active' => true,
                ]);

                Branch::create([
                    'object_type' => DebtorType::class,
                    'object_id' => $dt->id,
                    'location' => $branches[$i],
                ]);
            }
        }
    }
}
