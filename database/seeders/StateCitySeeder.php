<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Scopes\BranchScope;
use App\Models\State;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StateCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('../SYSTEM AT-EASE SETTING.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("Excel file not found at: {$filePath}");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];

        // --- COUNTRY sheet (row 2 = header, row 3+ = data) ---
        // Columns: [1] country name, [2] code, [3] capital
        $countrySheet = $spreadsheet->getSheetByName('COUNTRY');
        $countryRows = $countrySheet->toArray();

        foreach ($countryRows as $i => $row) {
            if ($i < 3) continue; // skip title + header rows

            $countryName = trim($row[1] ?? '');
            $countryCode = trim($row[2] ?? '');

            if (empty($countryName)) continue;

            Country::updateOrCreate(
                ['name' => $countryName],
                ['code' => $countryCode, 'is_active' => true]
            );
        }

        $this->command->info('Countries seeded successfully.');

        // --- CITY sheet (columnar layout: row 3 = state names, row 4+ = cities) ---
        $sheet = $spreadsheet->getSheetByName('CITY');
        $rows = $sheet->toArray();

        // Get Malaysia country for states
        $country = Country::where('name', 'MALAYSIA')->first();

        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];

        // Row 3 (index 3) has state names in columns 2-15
        $stateRow = $rows[3];

        // Iterate each state column (columns 2 through 15)
        for ($col = 2; $col <= 15; $col++) {
            $stateName = trim($stateRow[$col] ?? '');
            if (empty($stateName)) {
                continue;
            }

            // Create state
            $state = State::updateOrCreate(
                ['name' => $stateName, 'country_id' => $country->id],
                ['is_active' => true]
            );

            // Read cities below (starting from row index 4)
            for ($row = 4; $row < count($rows); $row++) {
                $cityName = trim($rows[$row][$col] ?? '');
                if (empty($cityName)) {
                    continue;
                }

                // Create area for each branch
                foreach ($branches as $location) {
                    $existing = Area::withoutGlobalScope(BranchScope::class)
                        ->whereHas('branch', fn($q) => $q->where('location', $location))
                        ->where('name', $cityName)
                        ->where('state_id', $state->id)
                        ->first();

                    if (!$existing) {
                        $area = Area::withoutGlobalScope(BranchScope::class)->create([
                            'name' => $cityName,
                            'state_id' => $state->id,
                            'is_active' => true,
                        ]);

                        Branch::create([
                            'object_type' => Area::class,
                            'object_id' => $area->id,
                            'location' => $location,
                        ]);
                    }
                }
            }
        }

        $this->command->info('States and cities seeded successfully.');
    }
}
