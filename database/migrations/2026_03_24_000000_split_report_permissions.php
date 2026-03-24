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
            'report.production.view',
            'report.sales.view',
            'report.stock.view',
            'report.earning.view',
            'report.service.view',
            'report.technician_stock.view',
        ];

        // Create the new permissions
        foreach ($newPermissions as $permission) {
            $exists = DB::table('permissions')->where('name', $permission)->where('guard_name', 'web')->exists();

            if (!$exists) {
                DB::table('permissions')->insert([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Migrate existing report.view assignments to all 6 new permissions
        $oldPermission = DB::table('permissions')->where('name', 'report.view')->where('guard_name', 'web')->first();

        if ($oldPermission) {
            // Get all roles that have report.view
            $rolePermissions = DB::table('role_has_permissions')->where('permission_id', $oldPermission->id)->get();

            foreach ($rolePermissions as $rolePermission) {
                foreach ($newPermissions as $permName) {
                    $newPerm = DB::table('permissions')->where('name', $permName)->where('guard_name', 'web')->first();

                    if ($newPerm) {
                        $exists = DB::table('role_has_permissions')
                            ->where('permission_id', $newPerm->id)
                            ->where('role_id', $rolePermission->role_id)
                            ->exists();

                        if (!$exists) {
                            DB::table('role_has_permissions')->insert([
                                'permission_id' => $newPerm->id,
                                'role_id' => $rolePermission->role_id,
                            ]);
                        }
                    }
                }
            }

            // Get all direct user permissions for report.view
            $modelPermissions = DB::table('model_has_permissions')->where('permission_id', $oldPermission->id)->get();

            foreach ($modelPermissions as $modelPermission) {
                foreach ($newPermissions as $permName) {
                    $newPerm = DB::table('permissions')->where('name', $permName)->where('guard_name', 'web')->first();

                    if ($newPerm) {
                        $exists = DB::table('model_has_permissions')
                            ->where('permission_id', $newPerm->id)
                            ->where('model_id', $modelPermission->model_id)
                            ->where('model_type', $modelPermission->model_type)
                            ->exists();

                        if (!$exists) {
                            DB::table('model_has_permissions')->insert([
                                'permission_id' => $newPerm->id,
                                'model_id' => $modelPermission->model_id,
                                'model_type' => $modelPermission->model_type,
                            ]);
                        }
                    }
                }
            }

            // Remove old permission assignments and the permission itself
            DB::table('role_has_permissions')->where('permission_id', $oldPermission->id)->delete();
            DB::table('model_has_permissions')->where('permission_id', $oldPermission->id)->delete();
            DB::table('permissions')->where('id', $oldPermission->id)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create report.view
        $exists = DB::table('permissions')->where('name', 'report.view')->where('guard_name', 'web')->exists();

        if (!$exists) {
            DB::table('permissions')->insert([
                'name' => 'report.view',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $newPermissions = [
            'report.production.view',
            'report.sales.view',
            'report.stock.view',
            'report.earning.view',
            'report.service.view',
            'report.technician_stock.view',
        ];

        DB::table('permissions')->whereIn('name', $newPermissions)->where('guard_name', 'web')->delete();
    }
};
