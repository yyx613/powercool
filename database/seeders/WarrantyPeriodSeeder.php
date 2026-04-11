<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\WarrantyPeriod;
use Illuminate\Database\Seeder;

class WarrantyPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];

        $warranties = [
            ['warranty_code' => 'C1MW', 'name' => '1 MONTHS FULL WARRANTY FROM INVOICE DATE (EXCLUDED WEAR & TEAR)', 'period' => 1],
            ['warranty_code' => 'C6MW', 'name' => '6 MONTHS FULL WARRANTY FROM INVOICE DATE (EXCLUDED WEAR & TEAR)', 'period' => 6],
            ['warranty_code' => 'C3MW', 'name' => '3 MONTHS FULL WARRANTY FROM INVOICE DATE (EXCLUDED WEAR & TEAR)', 'period' => 3],
            ['warranty_code' => 'CSOW', 'name' => 'SERVICE OVER WARRANTY', 'period' => 0],
            ['warranty_code' => 'CWLSP', 'name' => '1 YEAR WARRANTY ON LISTED SPARE PARTS FROM INV. DATE, EXCLUDED SERVICE,LABOUR & TRANSPORTATION CHRG.', 'period' => 12],
            ['warranty_code' => 'CWC', 'name' => '1 YEAR WARRANTY ON COMPRESSOR ONLY FROM INVOICE DATE, EXCLUDED SERVICE, LABOUR & TRANSPORT CHARGE', 'period' => 12],
            ['warranty_code' => 'CW', 'name' => '1ST YEAR FULL WARRANTY FROM INVOICE DATE (EXCLUDED WEAR & TEAR)', 'period' => 12],
            ['warranty_code' => 'C3W', 'name' => '3 YEARS WARRANTY ON COMPRESSOR ONLY FROM INVOICE DATE, EXCLUDED LABOUR & TRANSPORT CHARGES', 'period' => 36],
            ['warranty_code' => 'C5W', 'name' => '5 YEARS WARRANTY ON COMPRESSOR ONLY FROM INVOICE DATE (LIMITED TO 1 TIME ONLY) - T & C APPLY', 'period' => 60],
            ['warranty_code' => 'C7DW', 'name' => '7 DAYS MANUFACTURER DEFECT WARRANTY', 'period' => 0],
            ['warranty_code' => 'OEMW', 'name' => '3 DAYS WARRANTY FOR COOLING ONLY FROM INV.DATE, EXCLUDED WEAR TEAR, SERVICE, LABOUR &TRANSPORT CHRG.', 'period' => 0],
        ];

        foreach ($warranties as $warrantyData) {
            $wp = WarrantyPeriod::updateOrCreate(
                ['warranty_code' => $warrantyData['warranty_code']],
                [
                    'name' => $warrantyData['name'],
                    'period' => $warrantyData['period'],
                    'is_active' => true,
                ]
            );

            foreach ($branches as $branch) {
                $existingBranch = Branch::where('object_type', WarrantyPeriod::class)
                    ->where('object_id', $wp->id)
                    ->where('location', $branch)
                    ->first();

                if (!$existingBranch) {
                    Branch::create([
                        'object_type' => WarrantyPeriod::class,
                        'object_id' => $wp->id,
                        'location' => $branch,
                    ]);
                }
            }
        }
    }
}
