<?php

use App\Models\Branch;
use App\Models\Milestone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Default milestones are global templates seeded without a branch, so the
 * BranchScope hid them from branch-scoped users (empty checklist on the form,
 * and missing from a task's milestones on the mobile app). Assign every
 * default milestone to every real branch so they are visible everywhere.
 */
return new class extends Migration
{
    /** Real branches (excludes LOCATION_EVERY). */
    private array $locations = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];

    public function up(): void
    {
        $now = now();
        $defaults = DB::table('milestones')
            ->where('is_custom', false)
            ->whereNull('deleted_at')
            ->pluck('id');

        $rows = [];
        foreach ($defaults as $milestoneId) {
            foreach ($this->locations as $location) {
                $exists = DB::table('branches')
                    ->where('object_type', Milestone::class)
                    ->where('object_id', $milestoneId)
                    ->where('location', $location)
                    ->exists();
                if (! $exists) {
                    $rows[] = [
                        'object_type' => Milestone::class,
                        'object_id' => $milestoneId,
                        'location' => $location,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (! empty($rows)) {
            DB::table('branches')->insert($rows);
        }
    }

    public function down(): void
    {
        $defaults = DB::table('milestones')
            ->where('is_custom', false)
            ->pluck('id');

        DB::table('branches')
            ->where('object_type', Milestone::class)
            ->whereIn('object_id', $defaults)
            ->whereIn('location', $this->locations)
            ->delete();
    }
};
