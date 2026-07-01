<?php

use App\Models\Milestone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private int $type = Milestone::TYPE_SITE_VISIT;

    public function up(): void
    {
        // 1) Give milestones a defined display order, independent of insertion id.
        if (! Schema::hasColumn('milestones', 'sort')) {
            Schema::table('milestones', function (Blueprint $table) {
                $table->unsignedInteger('sort')->nullable()->after('name');
            });
        }
        // Preserve current ordering everywhere (other task types are unaffected).
        DB::table('milestones')->whereNull('sort')->update(['sort' => DB::raw('id')]);

        // 2) Update the Site Visit (sale task) default milestone flow.
        // Rename the two obsolete default steps in place so any history keeps a valid label.
        DB::table('milestones')->where('type', $this->type)->where('is_custom', false)
            ->where('name', 'Measurement Remark (Attach Photo)')
            ->update(['name' => 'Purpose / Business Nature']);
        DB::table('milestones')->where('type', $this->type)->where('is_custom', false)
            ->where('name', 'Survey Feedback')
            ->update(['name' => 'Photo (Customer, Shop)']);

        // Ensure all six defaults exist and are ordered (idempotent).
        $order = [
            'Check In' => 1,
            'Purpose / Business Nature' => 2,
            'Photo (Customer, Shop)' => 3,
            'Payment Collection' => 4,
            'Check Out' => 5,
            'Result (Potential / No Potential)' => 6,
        ];
        $now = now();
        foreach ($order as $name => $sort) {
            $existing = DB::table('milestones')->where('type', $this->type)
                ->where('is_custom', false)->where('name', $name)->first();

            if ($existing) {
                DB::table('milestones')->where('id', $existing->id)
                    ->update(['sort' => $sort, 'updated_at' => $now]);
            } else {
                DB::table('milestones')->insert([
                    'type' => $this->type,
                    'name' => $name,
                    'is_custom' => false,
                    'sort' => $sort,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // A new 'Payment Collection' milestone exists now; refresh the cached id list.
        Cache::forget('payment_collection_ids');
    }

    public function down(): void
    {
        // Remove the newly added default steps (only if never used).
        foreach (['Payment Collection', 'Result (Potential / No Potential)'] as $name) {
            $ms = DB::table('milestones')->where('type', $this->type)
                ->where('is_custom', false)->where('name', $name)->first();
            if ($ms && ! DB::table('task_milestone')->where('milestone_id', $ms->id)->exists()) {
                DB::table('milestones')->where('id', $ms->id)->delete();
            }
        }

        // Restore the previous default step names.
        DB::table('milestones')->where('type', $this->type)->where('is_custom', false)
            ->where('name', 'Purpose / Business Nature')
            ->update(['name' => 'Measurement Remark (Attach Photo)']);
        DB::table('milestones')->where('type', $this->type)->where('is_custom', false)
            ->where('name', 'Photo (Customer, Shop)')
            ->update(['name' => 'Survey Feedback']);

        Cache::forget('payment_collection_ids');

        if (Schema::hasColumn('milestones', 'sort')) {
            Schema::table('milestones', function (Blueprint $table) {
                $table->dropColumn('sort');
            });
        }
    }
};
