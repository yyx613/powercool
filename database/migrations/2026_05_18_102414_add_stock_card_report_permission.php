<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('permissions')
            ->where('name', 'report.stock_card')
            ->where('guard_name', 'web')
            ->exists();

        if (! $exists) {
            DB::table('permissions')->insert([
                'name' => 'report.stock_card',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $newId = DB::table('permissions')
            ->where('name', 'report.stock_card')
            ->where('guard_name', 'web')
            ->value('id');

        $stockReportId = DB::table('permissions')
            ->where('name', 'report.stock')
            ->where('guard_name', 'web')
            ->value('id');

        if ($newId && $stockReportId) {
            $roleIds = DB::table('role_has_permissions')
                ->where('permission_id', $stockReportId)
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                $alreadyAssigned = DB::table('role_has_permissions')
                    ->where('permission_id', $newId)
                    ->where('role_id', $roleId)
                    ->exists();

                if (! $alreadyAssigned) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $newId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'report.stock_card')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId) {
            DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
