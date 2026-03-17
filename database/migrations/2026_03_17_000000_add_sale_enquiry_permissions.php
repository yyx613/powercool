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
        $permissions = [
            'sale_enquiry.view',
            'sale_enquiry.create',
            'sale_enquiry.edit',
            'sale_enquiry.delete',
        ];

        foreach ($permissions as $permission) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'sale_enquiry.view',
            'sale_enquiry.create',
            'sale_enquiry.edit',
            'sale_enquiry.delete',
        ])->where('guard_name', 'web')->delete();
    }
};
