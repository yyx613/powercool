<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Scopes\BranchScope;
use App\Models\Vehicle;
use DateTimeInterface;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VehicleSeeder extends Seeder
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
            ->toArray(null, true, true, false);

        $typeMap = [
            'CAR'   => Vehicle::TYPE_CAR,
            'LORRY' => Vehicle::TYPE_LORRY,
            'VAN'   => Vehicle::TYPE_VAN,
            'MOTOR' => Vehicle::TYPE_MOTOR,
        ];

        $branchMap = [
            'HQ'     => Branch::LOCATION_KL,
            'PENANG' => Branch::LOCATION_PENANG,
        ];

        $seeded = 0;
        $skipped = 0;

        foreach ($rows as $i => $row) {
            if ($i === 0) continue;

            $plate = trim($row[0] ?? '');
            if ($plate === '') continue;

            $branchKey = strtoupper(trim($row[12] ?? ''));
            if (!isset($branchMap[$branchKey])) {
                $skipped++;
                continue;
            }

            $typeKey = strtoupper(trim($row[11] ?? ''));
            if (!isset($typeMap[$typeKey])) {
                $skipped++;
                continue;
            }

            $tarikh = $row[8] ?? null;
            if ($tarikh instanceof DateTimeInterface) {
                $tarikh = $tarikh->format('Y-m-d');
            } elseif (is_string($tarikh)) {
                $tarikh = trim($tarikh);
            }

            $vehicle = Vehicle::withoutGlobalScope(BranchScope::class)->updateOrCreate(
                ['plate_number' => $plate],
                [
                    'chasis'             => trim((string) ($row[1] ?? '')),
                    'buatan_nama_model'  => trim((string) ($row[2] ?? '')),
                    'keupayaan_enjin'    => is_null($row[3] ?? null) ? null : (string) $row[3],
                    'bahan_bakar'        => trim((string) ($row[4] ?? '')),
                    'status_asal'        => trim((string) ($row[5] ?? '')),
                    'kelas_kegunaan'     => trim((string) ($row[6] ?? '')),
                    'jenis_badan'        => trim((string) ($row[7] ?? '')),
                    'tarikh_pendaftaran' => $tarikh,
                    'department'         => trim((string) ($row[10] ?? '')),
                    'type'               => $typeMap[$typeKey],
                    'status'             => Vehicle::STATUS_ACTIVE,
                ]
            );

            $alreadyLinked = Branch::where('object_type', Vehicle::class)
                ->where('object_id', $vehicle->id)
                ->exists();

            if (!$alreadyLinked) {
                Branch::create([
                    'object_type' => Vehicle::class,
                    'object_id'   => $vehicle->id,
                    'location'    => $branchMap[$branchKey],
                ]);
            }

            $seeded++;
        }

        $this->command->info("Vehicles seeded: {$seeded}, skipped: {$skipped}.");
    }
}
