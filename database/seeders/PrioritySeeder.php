<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Priority;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            [
                'priority' => 'P1',
                'name' => 'Critical',
                'description' => 'Operations stopped, severe customer impact, safety or compliance risk',
                'response_time' => 'Immediate (0-4 hrs)',
                'order' => 1,
            ],
            [
                'priority' => 'P2',
                'name' => 'High',
                'description' => 'Major impact but operation still running, confirmed order affected',
                'response_time' => 'Within 24 hrs',
                'order' => 2,
            ],
            [
                'priority' => 'P3',
                'name' => 'Medium',
                'description' => 'Partial impact, workaround available, no immediate customer loss',
                'response_time' => 'Within 3 working days',
                'order' => 3,
            ],
            [
                'priority' => 'P4',
                'name' => 'Low',
                'description' => 'General inquiry, improvement request, no operational impact',
                'response_time' => 'Within 7 working days',
                'order' => 4,
            ],
        ];

        // Seed for KL branch
        foreach ($priorities as $data) {
            $existing = Priority::withoutGlobalScope(BranchScope::class)
                ->whereHas('branch', fn($q) => $q->where('location', Branch::LOCATION_KL))
                ->where('priority', $data['priority'])
                ->first();

            if (!$existing) {
                // Check if there's an old-style record to update (e.g., "P1 - Critical")
                $oldStyleName = $data['priority'] . ' - ' . $data['name'];
                $oldRecord = Priority::withoutGlobalScope(BranchScope::class)
                    ->whereHas('branch', fn($q) => $q->where('location', Branch::LOCATION_KL))
                    ->where('name', $oldStyleName)
                    ->first();

                if ($oldRecord) {
                    $oldRecord->update($data);
                } else {
                    $priority = Priority::withoutGlobalScope(BranchScope::class)->create($data);
                    Branch::create([
                        'object_type' => Priority::class,
                        'object_id' => $priority->id,
                        'location' => Branch::LOCATION_KL,
                    ]);
                }
            } else {
                $existing->update($data);
            }
        }

        // Seed for Penang branch
        foreach ($priorities as $data) {
            $existing = Priority::withoutGlobalScope(BranchScope::class)
                ->whereHas('branch', fn($q) => $q->where('location', Branch::LOCATION_PENANG))
                ->where('priority', $data['priority'])
                ->first();

            if (!$existing) {
                // Check if there's an old-style record to update (e.g., "P1 - Critical")
                $oldStyleName = $data['priority'] . ' - ' . $data['name'];
                $oldRecord = Priority::withoutGlobalScope(BranchScope::class)
                    ->whereHas('branch', fn($q) => $q->where('location', Branch::LOCATION_PENANG))
                    ->where('name', $oldStyleName)
                    ->first();

                if ($oldRecord) {
                    $oldRecord->update($data);
                } else {
                    $priority = Priority::withoutGlobalScope(BranchScope::class)->create($data);
                    Branch::create([
                        'object_type' => Priority::class,
                        'object_id' => $priority->id,
                        'location' => Branch::LOCATION_PENANG,
                    ]);
                }
            } else {
                $existing->update($data);
            }
        }
    }
}
