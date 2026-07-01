<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Locks in server-side sorting of the notification-listing "Description" (idx 2)
 * and "No." (idx 0) columns.
 *
 * Description is rendered from data['desc'], where `data` is a TEXT column
 * holding JSON, so it sorts via JSON_EXTRACT. "No." is a per-page loop index, so
 * it maps to the listing's natural created_at order (the id is a UUID, not
 * sequential).
 */
class ListingSortNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private function userWithNotificationView(): User
    {
        Permission::firstOrCreate(['name' => 'notification.view', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('notification.view');

        return $user->fresh('roles');
    }

    /**
     * Inserts three directed (always-visible) notifications for the user with
     * distinct desc values and distinct created_at timestamps. Returns the descs
     * keyed by created_at order (oldest first).
     *
     * @return array<string> [oldest_desc, middle_desc, newest_desc]
     */
    private function makeThreeNotifications(User $user): array
    {
        $token = 'SORTDESC' . uniqid();

        // desc chosen so that alphabetical asc order (A,B,C) differs from
        // created_at order, proving the sort acts on desc not on insertion.
        $rows = [
            ['desc' => $token . ' C oldest',  'created_at' => now()->subMinutes(30)],
            ['desc' => $token . ' A middle',  'created_at' => now()->subMinutes(20)],
            ['desc' => $token . ' B newest',  'created_at' => now()->subMinutes(10)],
        ];

        foreach ($rows as $row) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'sale-enquiry-assigned',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode(['desc' => $row['desc']]),
                'read_at' => null,
                'created_at' => $row['created_at'],
                'updated_at' => $row['created_at'],
            ]);
        }

        return [$rows[0]['desc'], $rows[1]['desc'], $rows[2]['desc']];
    }

    private function orderedDescs(string $dir, int $column): array
    {
        return collect(
            $this->getJson(route('notification.get_data', [
                'order' => [['column' => $column, 'dir' => $dir]],
            ]))->assertOk()->json('data')
        )->pluck('desc')->values()->all();
    }

    public function test_description_column_sorts_ascending(): void
    {
        $user = $this->userWithNotificationView();
        [$oldest, $middle, $newest] = $this->makeThreeNotifications($user);
        $this->actingAs($user);

        $descs = $this->orderedDescs('asc', 2);

        // Alphabetical asc: "A middle", "B newest", "C oldest".
        $this->assertSame([$middle, $newest, $oldest], $descs);
    }

    public function test_description_column_sorts_descending(): void
    {
        $user = $this->userWithNotificationView();
        [$oldest, $middle, $newest] = $this->makeThreeNotifications($user);
        $this->actingAs($user);

        $descs = $this->orderedDescs('desc', 2);

        // Alphabetical desc: "C oldest", "B newest", "A middle".
        $this->assertSame([$oldest, $newest, $middle], $descs);
    }

    public function test_no_column_sort_runs_and_uses_created_at_order(): void
    {
        $user = $this->userWithNotificationView();
        [$oldest, $middle, $newest] = $this->makeThreeNotifications($user);
        $this->actingAs($user);

        // Col 0 maps to created_at. Asc => oldest first.
        $ascDescs = $this->orderedDescs('asc', 0);
        $this->assertSame([$oldest, $middle, $newest], $ascDescs);

        // Desc => newest first.
        $descDescs = $this->orderedDescs('desc', 0);
        $this->assertSame([$newest, $middle, $oldest], $descDescs);
    }
}
