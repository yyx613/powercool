<?php

namespace Tests\Feature;

use App\Models\SaleEnquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Verifies the relation columns of the Sale Enquiry listing are server-side
 * sortable: Assigned Staff (idx 7) and Created By (idx 11), each ordered via a
 * correlated subquery against the users table. Isolation is by a unique search
 * keyword so the three seeded enquiries are the only rows returned.
 */
class ListingSortSaleEnquiryTest extends TestCase
{
    use DatabaseTransactions;

    private function actingUser(): User
    {
        Permission::firstOrCreate(['name' => 'sale_enquiry.view', 'guard_name' => 'web']);
        // No branch => BranchScope adds no filter, so all seeded rows are visible.
        $user = User::factory()->create();
        $user->givePermissionTo('sale_enquiry.view');

        return $user;
    }

    /**
     * Seed three enquiries sharing a unique keyword in their name, each with a
     * distinct assigned user and a distinct created-by user. Returns the keyword.
     */
    private function seedEnquiries(): string
    {
        $keyword = 'SORTKEY' . uniqid();

        $assigned = ['Charlie', 'Alice', 'Bob'];
        $createdBy = ['Zoe', 'Yan', 'Xena'];

        foreach ([0, 1, 2] as $i) {
            SaleEnquiry::factory()->create([
                'name' => $keyword . ' ' . $i,
                'assigned_user_id' => User::factory()->create(['name' => $assigned[$i]])->id,
                'created_by' => User::factory()->create(['name' => $createdBy[$i]])->id,
            ]);
        }

        return $keyword;
    }

    private function fetch(string $keyword, int $column, string $dir): Collection
    {
        return collect($this->getJson(route('sale_enquiry.get_data', [
            'search' => ['value' => $keyword],
            'order' => [['column' => $column, 'dir' => $dir]],
        ]))->assertOk()->json('data'));
    }

    public function test_assigned_staff_column_is_server_side_sortable(): void
    {
        $user = $this->actingUser();
        $keyword = $this->seedEnquiries();
        $this->actingAs($user);

        $asc = $this->fetch($keyword, 7, 'asc')->pluck('assigned_user')->all();
        $this->assertSame(['Alice', 'Bob', 'Charlie'], $asc);

        $desc = $this->fetch($keyword, 7, 'desc')->pluck('assigned_user')->all();
        $this->assertSame(['Charlie', 'Bob', 'Alice'], $desc);
    }

    public function test_created_by_column_is_server_side_sortable(): void
    {
        $user = $this->actingUser();
        $keyword = $this->seedEnquiries();
        $this->actingAs($user);

        $asc = $this->fetch($keyword, 11, 'asc')->pluck('created_by_user')->all();
        $this->assertSame(['Xena', 'Yan', 'Zoe'], $asc);

        $desc = $this->fetch($keyword, 11, 'desc')->pluck('created_by_user')->all();
        $this->assertSame(['Zoe', 'Yan', 'Xena'], $desc);
    }
}
