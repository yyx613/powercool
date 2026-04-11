<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];

        $platforms = [
            ['name' => 'SYSTEM', 'can_submit_einvoice' => true],
            ['name' => 'WOO-COMMERCE', 'can_submit_einvoice' => false],
            ['name' => 'TIKTOK', 'can_submit_einvoice' => false],
            ['name' => 'LAZADA', 'can_submit_einvoice' => false],
            ['name' => 'SHOPEE', 'can_submit_einvoice' => false],
            ['name' => 'TAOBAO', 'can_submit_einvoice' => false],
            ['name' => 'XHS', 'can_submit_einvoice' => false],
        ];

        foreach ($platforms as $platformData) {
            $platform = Platform::updateOrCreate(
                ['name' => $platformData['name']],
                [
                    'can_submit_einvoice' => $platformData['can_submit_einvoice'],
                    'is_active' => true,
                ]
            );

            foreach ($branches as $branch) {
                $existingBranch = Branch::where('object_type', Platform::class)
                    ->where('object_id', $platform->id)
                    ->where('location', $branch)
                    ->first();

                if (!$existingBranch) {
                    Branch::create([
                        'object_type' => Platform::class,
                        'object_id' => $platform->id,
                        'location' => $branch,
                    ]);
                }
            }
        }
    }
}
