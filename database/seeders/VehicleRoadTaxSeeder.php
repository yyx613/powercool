<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Scopes\BranchScope;
use App\Models\Vehicle;
use App\Models\VehicleService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class VehicleRoadTaxSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = base_path('../VEHICLE LIST.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("Excel file not found at: {$filePath}");
            return;
        }

        $rows = IOFactory::load($filePath)
            ->getActiveSheet()
            ->toArray(null, true, false, false);

        $branchMap = [
            'HQ'     => Branch::LOCATION_KL,
            'PENANG' => Branch::LOCATION_PENANG,
        ];

        $seeded = 0;
        $skippedNoExpiry = 0;
        $skippedBranch = 0;
        $skippedNoVehicle = 0;

        foreach ($rows as $i => $row) {
            if ($i === 0) continue;

            $plate = trim($row[0] ?? '');
            if ($plate === '') continue;

            $expiryRaw = $row[9] ?? null;
            if ($expiryRaw === null || (is_string($expiryRaw) && trim($expiryRaw) === '')) {
                $skippedNoExpiry++;
                continue;
            }

            $branchKey = strtoupper(trim($row[12] ?? ''));
            if (!isset($branchMap[$branchKey])) {
                $skippedBranch++;
                continue;
            }

            $expiry = $expiryRaw;
            if ($expiry instanceof DateTimeInterface) {
                $expiry = $expiry->format('Y-m-d');
            } elseif (is_numeric($expiry)) {
                $expiry = ExcelDate::excelToDateTimeObject($expiry)->format('Y-m-d');
            } else {
                $expiry = trim((string) $expiry);
            }

            $vehicle = Vehicle::withoutGlobalScope(BranchScope::class)
                ->where('plate_number', $plate)
                ->first();

            if ($vehicle === null) {
                $skippedNoVehicle++;
                continue;
            }

            $service = VehicleService::withoutGlobalScope(BranchScope::class)->updateOrCreate(
                [
                    'vehicle_id' => $vehicle->id,
                    'type'       => 2,
                ],
                [
                    'date'      => $expiry,
                    'to_date'   => null,
                    'remind_at' => Carbon::parse($expiry)->subMonths(1),
                    'amount'    => null,
                ]
            );

            $alreadyLinked = Branch::where('object_type', VehicleService::class)
                ->where('object_id', $service->id)
                ->exists();

            if (!$alreadyLinked) {
                Branch::create([
                    'object_type' => VehicleService::class,
                    'object_id'   => $service->id,
                    'location'    => $branchMap[$branchKey],
                ]);
            }

            $seeded++;
        }

        $this->command->info(
            "Roadtax services seeded: {$seeded}, skipped (no expiry): {$skippedNoExpiry}, "
            . "skipped (branch): {$skippedBranch}, skipped (no vehicle): {$skippedNoVehicle}."
        );
    }
}
