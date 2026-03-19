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
        $oldPermission = DB::table('permissions')->where('name', 'notification.production_complete_notification')->first();
        $newPermission = DB::table('permissions')->where('name', 'notification.view_production_completed')->first();

        if ($oldPermission && $newPermission) {
            // Transfer role assignments from old permission to new permission (skip duplicates)
            $existingRoleIds = DB::table('role_has_permissions')
                ->where('permission_id', $newPermission->id)
                ->pluck('role_id')
                ->toArray();

            $roleAssignments = DB::table('role_has_permissions')
                ->where('permission_id', $oldPermission->id)
                ->whereNotIn('role_id', $existingRoleIds)
                ->get();

            foreach ($roleAssignments as $assignment) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $newPermission->id,
                    'role_id' => $assignment->role_id,
                ]);
            }

            // Transfer direct user assignments from old permission to new permission (skip duplicates)
            $existingModelIds = DB::table('model_has_permissions')
                ->where('permission_id', $newPermission->id)
                ->pluck('model_id')
                ->toArray();

            $userAssignments = DB::table('model_has_permissions')
                ->where('permission_id', $oldPermission->id)
                ->whereNotIn('model_id', $existingModelIds)
                ->get();

            foreach ($userAssignments as $assignment) {
                DB::table('model_has_permissions')->insert([
                    'permission_id' => $newPermission->id,
                    'model_id' => $assignment->model_id,
                    'model_type' => $assignment->model_type,
                ]);
            }

            // Remove old permission assignments and the permission itself
            DB::table('role_has_permissions')->where('permission_id', $oldPermission->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $oldPermission->id)->delete();
            DB::table('permissions')->where('id', $oldPermission->id)->delete();
        } elseif ($oldPermission) {
            // New permission doesn't exist yet — just rename the old one
            DB::table('permissions')->where('id', $oldPermission->id)->update([
                'name' => 'notification.view_production_completed',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create the old permission
        $exists = DB::table('permissions')->where('name', 'notification.production_complete_notification')->exists();
        if (!$exists) {
            DB::table('permissions')->insert([
                'name' => 'notification.production_complete_notification',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
