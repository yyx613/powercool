<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'key' => 'sst',
            'Name' => 'SST',
            'value' => 1.4
        ]);
        Setting::create([
            'key' => 'tax_code',
            'Name' => 'Tax Code',
            'value' => 'S-10'
        ]);
    }
}
