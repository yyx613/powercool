<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add production.force_complete permission
        DB::table('permissions')->insert([
            'name' => 'production.force_complete',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add production.check_in_milestone permission
        DB::table('permissions')->insert([
            'name' => 'production.check_in_milestone',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $forceCompleteId = DB::table('permissions')->where('name', 'production.force_complete')->value('id');
        $checkInMilestoneId = DB::table('permissions')->where('name', 'production.check_in_milestone')->value('id');

        // Assign production.force_complete to roles that had production.complete
        $completePermission = DB::table('permissions')->where('name', 'production.complete')->first();
        if ($completePermission) {
            $roleIds = DB::table('role_has_permissions')
                ->where('permission_id', $completePermission->id)
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $forceCompleteId)
                    ->where('role_id', $roleId)
                    ->exists();

                if (! $exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $forceCompleteId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }

        // Assign production.check_in_milestone to roles that had production.view
        // (previously check-in had no permission gate, so all production viewers could do it)
        $viewPermission = DB::table('permissions')->where('name', 'production.view')->first();
        if ($viewPermission) {
            $roleIds = DB::table('role_has_permissions')
                ->where('permission_id', $viewPermission->id)
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                $exists = DB::table('role_has_permissions')
                    ->where('permission_id', $checkInMilestoneId)
                    ->where('role_id', $roleId)
                    ->exists();

                if (! $exists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $checkInMilestoneId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $forceCompleteId = DB::table('permissions')->where('name', 'production.force_complete')->value('id');
        $checkInMilestoneId = DB::table('permissions')->where('name', 'production.check_in_milestone')->value('id');

        if ($forceCompleteId) {
            DB::table('role_has_permissions')->where('permission_id', $forceCompleteId)->delete();
        }
        if ($checkInMilestoneId) {
            DB::table('role_has_permissions')->where('permission_id', $checkInMilestoneId)->delete();
        }

        DB::table('permissions')->where('name', 'production.force_complete')->delete();
        DB::table('permissions')->where('name', 'production.check_in_milestone')->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
