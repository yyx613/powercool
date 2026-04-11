<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CreditTerm;
use Illuminate\Database\Seeder;

class CreditTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];
        $types = ['120 Days', '90 Days', '60 Days', '30 Days', '7 Days', 'Cash On Delivery'];

        for ($i = 0; $i < count($branches); $i++) {
            for ($j = 0; $j < count($types); $j++) {
                $ct = CreditTerm::create([
                    'name' => $types[$j],
                    'is_active' => true,
                ]);

                Branch::create([
                    'object_type' => CreditTerm::class,
                    'object_id' => $ct->id,
                    'location' => $branches[$i],
                ]);
            }
        }
    }
}
