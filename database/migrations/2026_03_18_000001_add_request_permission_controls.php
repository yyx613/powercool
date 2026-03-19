<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $guard = 'web';
        $now = now();

        $permissions = [
            'inventory.raw_material_request.create',
            'production_request.complete',
            'production.cancel',
            'production.complete',
        ];

        foreach ($permissions as $permission) {
            if (!DB::table('permissions')->where('name', $permission)->exists()) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => $guard,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Merge cancel permission into complete: transfer role assignments then delete cancel
        $cancelPerm = DB::table('permissions')->where('name', 'inventory.raw_material_request.cancel')->first();
        $completePerm = DB::table('permissions')->where('name', 'inventory.raw_material_request.complete')->first();

        if ($cancelPerm && $completePerm) {
            // Get roles that have cancel but not complete, and assign them complete
            $cancelRoleIds = DB::table('role_has_permissions')
                ->where('permission_id', $cancelPerm->id)
                ->pluck('role_id');

            $completeRoleIds = DB::table('role_has_permissions')
                ->where('permission_id', $completePerm->id)
                ->pluck('role_id');

            $missingRoleIds = $cancelRoleIds->diff($completeRoleIds);
            foreach ($missingRoleIds as $roleId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $completePerm->id,
                    'role_id' => $roleId,
                ]);
            }

            // Delete the cancel permission and its role assignments
            DB::table('role_has_permissions')->where('permission_id', $cancelPerm->id)->delete();
            DB::table('permissions')->where('id', $cancelPerm->id)->delete();
        } elseif ($cancelPerm) {
            // No complete permission exists yet, just rename cancel to complete
            DB::table('permissions')->where('id', $cancelPerm->id)->update(['name' => 'inventory.raw_material_request.complete']);
        }
    }

    public function down(): void
    {
        $permissions = [
            'inventory.raw_material_request.create',
            'production_request.complete',
            'production.cancel',
            'production.complete',
        ];

        foreach ($permissions as $permission) {
            $id = DB::table('permissions')->where('name', $permission)->value('id');
            if ($id) {
                DB::table('role_has_permissions')->where('permission_id', $id)->delete();
                DB::table('permissions')->where('id', $id)->delete();
            }
        }
    }
};
