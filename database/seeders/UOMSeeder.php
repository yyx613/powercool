<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\UOM;
use Illuminate\Database\Seeder;

class UOMSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];

        $uoms = ['MTH', 'PCS', 'TRIP', 'UNIT'];

        foreach ($uoms as $name) {
            $uom = UOM::withoutGlobalScopes()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );

            foreach ($branches as $branch) {
                $existingBranch = Branch::where('object_type', UOM::class)
                    ->where('object_id', $uom->id)
                    ->where('location', $branch)
                    ->first();

                if (!$existingBranch) {
                    Branch::create([
                        'object_type' => UOM::class,
                        'object_id' => $uom->id,
                        'location' => $branch,
                    ]);
                }
            }
        }
    }
}
