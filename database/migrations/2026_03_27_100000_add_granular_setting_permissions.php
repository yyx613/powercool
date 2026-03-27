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
        $newPermissions = [
            'setting.area.view',
            'setting.material_use.view',
            'setting.country.view',
            'setting.credit_term.view',
            'setting.currency.view',
            'setting.debtor_type.view',
            'setting.factory.view',
            'setting.milestone.view',
            'setting.payment_method.view',
            'setting.inventory_type.view',
            'setting.promotion.view',
            'setting.state.view',
            'setting.project_type.view',
            'setting.platform.view',
            'setting.priority.view',
            'setting.sales_agent.view',
            'setting.service.view',
            'setting.tax_rate.view',
            'setting.tax_rate.edit',
            'setting.sync.view',
            'setting.uom.view',
            'setting.warranty_period.view',
        ];

        $now = now();

        // Create new permissions
        foreach ($newPermissions as $permission) {
            $exists = DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Migrate role assignments from old setting.view to all new view permissions
        $oldViewPermission = DB::table('permissions')->where('name', 'setting.view')->where('guard_name', 'web')->first();
        if ($oldViewPermission) {
            $roleIds = DB::table('role_has_permissions')->where('permission_id', $oldViewPermission->id)->pluck('role_id');

            $newViewPermissions = collect($newPermissions)->filter(fn($p) => str_ends_with($p, '.view'));
            $newPermissionIds = DB::table('permissions')->whereIn('name', $newViewPermissions)->where('guard_name', 'web')->pluck('id');

            foreach ($roleIds as $roleId) {
                foreach ($newPermissionIds as $permissionId) {
                    $exists = DB::table('role_has_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $permissionId)
                        ->exists();
                    if (!$exists) {
                        DB::table('role_has_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $permissionId,
                        ]);
                    }
                }
            }
        }

        // Migrate role assignments from old setting.edit to setting.tax_rate.edit
        $oldEditPermission = DB::table('permissions')->where('name', 'setting.edit')->where('guard_name', 'web')->first();
        if ($oldEditPermission) {
            $roleIds = DB::table('role_has_permissions')->where('permission_id', $oldEditPermission->id)->pluck('role_id');
            $newEditPermission = DB::table('permissions')->where('name', 'setting.tax_rate.edit')->where('guard_name', 'web')->first();

            if ($newEditPermission) {
                foreach ($roleIds as $roleId) {
                    $exists = DB::table('role_has_permissions')
                        ->where('role_id', $roleId)
                        ->where('permission_id', $newEditPermission->id)
                        ->exists();
                    if (!$exists) {
                        DB::table('role_has_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $newEditPermission->id,
                        ]);
                    }
                }
            }
        }

        // Delete old permissions
        DB::table('permissions')->whereIn('name', ['setting.view', 'setting.edit'])->where('guard_name', 'web')->delete();

        // Clear Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = now();

        // Re-create old permissions
        foreach (['setting.view', 'setting.edit'] as $permission) {
            $exists = DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->exists();
            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Delete new granular permissions
        DB::table('permissions')->where('name', 'like', 'setting.%.view')->where('guard_name', 'web')->delete();
        DB::table('permissions')->where('name', 'setting.tax_rate.edit')->where('guard_name', 'web')->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
