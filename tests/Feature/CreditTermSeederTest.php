<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CreditTerm;
use Database\Seeders\CreditTermSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CreditTermSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_credit_term_seeder_creates_correct_records(): void
    {
        $initialCount = CreditTerm::withoutGlobalScopes()->count();
        $initialBranchCount = Branch::where('object_type', CreditTerm::class)->count();

        $this->seed(CreditTermSeeder::class);

        $expectedTerms = ['120 Days', '90 Days', '60 Days', '30 Days', '7 Days', 'Cash On Delivery'];

        // 6 terms x 2 branches = 12 new credit terms
        $newCount = CreditTerm::withoutGlobalScopes()->count() - $initialCount;
        $this->assertEquals(12, $newCount);

        // 12 new branch records
        $newBranchCount = Branch::where('object_type', CreditTerm::class)->count() - $initialBranchCount;
        $this->assertEquals(12, $newBranchCount);

        // Each branch should have all 6 terms
        foreach ([Branch::LOCATION_KL, Branch::LOCATION_PENANG] as $location) {
            $branchRecords = Branch::where('object_type', CreditTerm::class)
                ->where('location', $location)
                ->orderBy('id', 'desc')
                ->limit(6)
                ->get();

            $termNames = CreditTerm::withoutGlobalScopes()
                ->whereIn('id', $branchRecords->pluck('object_id'))
                ->pluck('name')
                ->toArray();

            foreach ($expectedTerms as $term) {
                $this->assertContains($term, $termNames);
            }
        }
    }

    public function test_all_seeded_credit_terms_are_active(): void
    {
        $this->seed(CreditTermSeeder::class);

        $latestTerms = CreditTerm::withoutGlobalScopes()
            ->orderBy('id', 'desc')
            ->limit(12)
            ->get();

        foreach ($latestTerms as $term) {
            $this->assertTrue((bool) $term->is_active);
        }
    }
}
