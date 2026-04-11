<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Factory;
use Illuminate\Database\Seeder;

class FactorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];
        $types = [
            'FACTORY 12',
            'FACTORY 17',
            'FACTORY 22',
            'VALDOR',
        ];

        foreach ($types as $type) {
            $factory = Factory::updateOrCreate(
                ['name' => $type],
            );

            foreach ($branches as $branch) {
                $existingBranch = Branch::where('object_type', Factory::class)
                    ->where('object_id', $factory->id)
                    ->where('location', $branch)
                    ->first();

                if (!$existingBranch) {
                    Branch::create([
                        'object_type' => Factory::class,
                        'object_id' => $factory->id,
                        'location' => $branch,
                    ]);
                }
            }
        }
    }
}
