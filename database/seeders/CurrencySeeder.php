<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];
        $types = ['MYR', 'SGD', 'USD', 'EURO', 'CNY', 'THB'];

        for ($i = 0; $i < count($branches); $i++) {
            for ($j = 0; $j < count($types); $j++) {
                $cu = Currency::create([
                    'name' => $types[$j],
                    'is_active' => true,
                ]);

                Branch::create([
                    'object_type' => Currency::class,
                    'object_id' => $cu->id,
                    'location' => $branches[$i],
                ]);
            }
        }
    }
}
