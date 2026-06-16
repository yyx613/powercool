<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The Country -> State cascading dropdown (used on the Customer and Sale Enquiry
 * forms) fetches states via the `country.get_states` endpoint. Those forms are
 * reachable without the Settings permission `setting.country.view`, and the
 * Country dropdown itself is injected by a view composer with no gate. The state
 * lookup must therefore be available to any authenticated user — otherwise the
 * "State" dropdown silently fails to load (HTTP 403).
 */
class StateLookupAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_user_without_country_setting_permission_can_load_states(): void
    {
        $country = Country::create(['name' => 'Testland', 'is_active' => true]);
        State::create(['country_id' => $country->id, 'name' => 'Test State', 'is_active' => true]);

        // A user with no permissions at all — definitely lacks setting.country.view.
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('country.get_states', $country));

        $this->assertNotEquals(
            403,
            $response->status(),
            'The state lookup must not be gated behind can:setting.country.view'
        );
        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Test State']);
    }
}
