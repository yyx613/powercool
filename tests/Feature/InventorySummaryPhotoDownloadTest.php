<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InventorySummaryPhotoDownloadTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Session::put('as_branch', Branch::LOCATION_KL);
        Storage::fake('local');
    }

    private function aProductAttachment(string $filename = 'widget.jpg', bool $seedFile = true): Attachment
    {
        if ($seedFile) {
            Storage::put(Attachment::PRODUCT_PATH.'/'.$filename, 'fake-image-bytes');
        }

        $product = Product::where('type', Product::TYPE_PRODUCT)->first();
        $this->assertNotNull($product, 'Expected at least one finished-good Product in the test DB');

        return Attachment::create([
            'object_type' => Product::class,
            'object_id' => $product->id,
            'src' => $filename,
        ]);
    }

    public function test_authenticated_user_with_permission_can_download_product_photo(): void
    {
        $this->actingAs(User::first());
        $attachment = $this->aProductAttachment('authz-'.uniqid().'.jpg');

        $response = $this->get(route('inventory_summary.download_photo', $attachment->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_user_without_permission_gets_403(): void
    {
        $attachment = $this->aProductAttachment('nopermz-'.uniqid().'.jpg');

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('inventory_summary.download_photo', $attachment->id));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $attachment = $this->aProductAttachment('guest-'.uniqid().'.jpg');

        $response = $this->get(route('inventory_summary.download_photo', $attachment->id));

        $response->assertRedirect(route('login'));
    }

    public function test_non_product_attachment_returns_404(): void
    {
        $this->actingAs(User::first());

        $customer = Customer::first();
        $this->assertNotNull($customer, 'Expected at least one Customer in the test DB');

        $filename = 'avatar-'.uniqid().'.jpg';
        Storage::put(Attachment::CUSTOMER_PATH.'/'.$filename, 'fake-bytes');
        $attachment = Attachment::create([
            'object_type' => Customer::class,
            'object_id' => $customer->id,
            'src' => $filename,
        ]);

        $response = $this->get(route('inventory_summary.download_photo', $attachment->id));

        $response->assertStatus(404);
    }

    public function test_missing_file_returns_404(): void
    {
        $this->actingAs(User::first());

        $attachment = $this->aProductAttachment('ghost-'.uniqid().'.jpg', seedFile: false);

        $response = $this->get(route('inventory_summary.download_photo', $attachment->id));

        $response->assertStatus(404);
    }
}
