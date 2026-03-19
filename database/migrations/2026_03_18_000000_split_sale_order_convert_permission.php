<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $guard = 'web';

        // Get the old permission
        $oldPermission = DB::table('permissions')->where('name', 'sale.sale_order.convert')->first();

        if (!$oldPermission) {
            // If old permission doesn't exist, just create the new ones
            DB::table('permissions')->insert([
                ['name' => 'sale.sale_order.convert_from', 'guard_name' => $guard, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'sale.sale_order.convert_to', 'guard_name' => $guard, 'created_at' => now(), 'updated_at' => now()],
            ]);
            return;
        }

        // Insert new permissions
        DB::table('permissions')->insert([
            ['name' => 'sale.sale_order.convert_from', 'guard_name' => $guard, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'sale.sale_order.convert_to', 'guard_name' => $guard, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $convertFromId = DB::table('permissions')->where('name', 'sale.sale_order.convert_from')->value('id');
        $convertToId = DB::table('permissions')->where('name', 'sale.sale_order.convert_to')->value('id');

        // Copy role associations from old permission to both new ones
        $roleAssociations = DB::table('role_has_permissions')
            ->where('permission_id', $oldPermission->id)
            ->get();

        $newAssociations = [];
        foreach ($roleAssociations as $assoc) {
            $newAssociations[] = ['permission_id' => $convertFromId, 'role_id' => $assoc->role_id];
            $newAssociations[] = ['permission_id' => $convertToId, 'role_id' => $assoc->role_id];
        }

        if (!empty($newAssociations)) {
            DB::table('role_has_permissions')->insert($newAssociations);
        }

        // Delete old permission associations and permission
        DB::table('role_has_permissions')->where('permission_id', $oldPermission->id)->delete();
        DB::table('permissions')->where('id', $oldPermission->id)->delete();
    }

    public function down(): void
    {
        $guard = 'web';

        // Recreate the old permission
        DB::table('permissions')->insert([
            'name' => 'sale.sale_order.convert',
            'guard_name' => $guard,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $oldId = DB::table('permissions')->where('name', 'sale.sale_order.convert')->value('id');
        $convertFromId = DB::table('permissions')->where('name', 'sale.sale_order.convert_from')->value('id');
        $convertToId = DB::table('permissions')->where('name', 'sale.sale_order.convert_to')->value('id');

        // Copy role associations from convert_from back to the old permission (deduplicated)
        if ($convertFromId) {
            $roles = DB::table('role_has_permissions')
                ->where('permission_id', $convertFromId)
                ->pluck('role_id');

            foreach ($roles as $roleId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $oldId,
                    'role_id' => $roleId,
                ]);
            }
        }

        // Delete new permissions
        if ($convertFromId) {
            DB::table('role_has_permissions')->where('permission_id', $convertFromId)->delete();
            DB::table('permissions')->where('id', $convertFromId)->delete();
        }
        if ($convertToId) {
            DB::table('role_has_permissions')->where('permission_id', $convertToId)->delete();
            DB::table('permissions')->where('id', $convertToId)->delete();
        }
    }
};
