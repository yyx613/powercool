<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Milestone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MilestoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $data = [];
        $sort = 1;

        foreach (Milestone::LIST as $key => $value) {
            for ($i=0; $i < count($value); $i++) {
                $data[] = [
                    'type' => $key,
                    'name' => $value[$i],
                    'sort' => $sort++,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }
        Milestone::insert($data);

        // Default milestones are global templates. Assign each to every real
        // branch so the BranchScope shows them to branch-scoped users.
        $locations = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];
        $branchRows = [];
        foreach (Milestone::withoutGlobalScopes()->pluck('id') as $milestoneId) {
            foreach ($locations as $location) {
                $branchRows[] = [
                    'object_type' => Milestone::class,
                    'object_id' => $milestoneId,
                    'location' => $location,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        if (! empty($branchRows)) {
            Branch::insert($branchRows);
        }
    }
}
