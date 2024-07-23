<?php

namespace Database\Seeders;

use App\Models\Milestone;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MilestoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $data = [];

        foreach (Milestone::LIST as $key => $value) {
            for ($i=0; $i < count($value); $i++) { 
                $data[] = [
                    'type' => $key,
                    'name' => $value[$i],
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }
        Milestone::insert($data);
    }
}
