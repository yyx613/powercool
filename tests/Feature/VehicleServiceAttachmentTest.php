<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Branch;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehicleServiceAttachmentTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::first();
        $this->actingAs($user);
        Session::put('as_branch', Branch::LOCATION_KL);

        Storage::fake('local');
    }

    public function test_can_create_vehicle_service_with_pdf_attachments(): void
    {
        $vehicle = Vehicle::first();

        $response = $this->post(route('vehicle_service.upsert'), [
            'vehicle' => $vehicle->id,
            'service' => 1, // Insurance
            'date' => '2026-01-01',
            'to_date' => '2027-01-01',
            'reminder_months' => 2,
            'service_amount' => 1000,
            'name' => ['Test Item'],
            'amount' => [500],
            'warranty_expiry_date' => [null],
            'warranty_term' => [null],
            'attachment' => [
                UploadedFile::fake()->create('document1.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('document2.pdf', 200, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect(route('vehicle_service.create'));

        $service = VehicleService::latest()->first();
        $this->assertCount(2, $service->attachments);
        $this->assertEquals(VehicleService::class, $service->attachments->first()->object_type);

        Storage::disk('local')->assertExists(Attachment::VEHICLE_SERVICE_PATH . '/' . $service->attachments->first()->src);
    }

    public function test_can_create_vehicle_service_without_attachments(): void
    {
        $vehicle = Vehicle::first();

        $response = $this->post(route('vehicle_service.upsert'), [
            'vehicle' => $vehicle->id,
            'service' => 1,
            'date' => '2026-01-01',
            'to_date' => '2027-01-01',
            'reminder_months' => 2,
            'service_amount' => 1000,
            'name' => ['Test Item'],
            'amount' => [500],
            'warranty_expiry_date' => [null],
            'warranty_term' => [null],
        ]);

        $response->assertRedirect(route('vehicle_service.create'));

        $service = VehicleService::latest()->first();
        $this->assertCount(0, $service->attachments);
    }

    public function test_rejects_non_pdf_attachments(): void
    {
        $vehicle = Vehicle::first();

        $response = $this->post(route('vehicle_service.upsert'), [
            'vehicle' => $vehicle->id,
            'service' => 1,
            'date' => '2026-01-01',
            'to_date' => '2027-01-01',
            'reminder_months' => 2,
            'service_amount' => 1000,
            'name' => ['Test Item'],
            'amount' => [500],
            'warranty_expiry_date' => [null],
            'warranty_term' => [null],
            'attachment' => [
                UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg'),
            ],
        ]);

        $response->assertSessionHasErrors('attachment.0');
    }

    public function test_can_add_attachments_when_editing_vehicle_service(): void
    {
        $vehicle = Vehicle::first();

        // Create service first
        $this->post(route('vehicle_service.upsert'), [
            'vehicle' => $vehicle->id,
            'service' => 1,
            'date' => '2026-01-01',
            'to_date' => '2027-01-01',
            'reminder_months' => 2,
            'service_amount' => 1000,
            'name' => ['Test Item'],
            'amount' => [500],
            'warranty_expiry_date' => [null],
            'warranty_term' => [null],
            'attachment' => [
                UploadedFile::fake()->create('first.pdf', 100, 'application/pdf'),
            ],
        ]);

        $service = VehicleService::latest()->first();
        $this->assertCount(1, $service->attachments);

        // Edit and add more attachments
        $response = $this->post(route('vehicle_service.upsert', ['service' => $service]), [
            'vehicle' => $vehicle->id,
            'service' => 1,
            'date' => '2026-01-01',
            'to_date' => '2027-01-01',
            'reminder_months' => 2,
            'service_amount' => 1000,
            'name' => ['Test Item'],
            'amount' => [500],
            'warranty_expiry_date' => [null],
            'warranty_term' => [null],
            'attachment' => [
                UploadedFile::fake()->create('second.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect(route('vehicle_service.index'));

        $service->refresh();
        $this->assertCount(2, $service->attachments);
    }

    public function test_edit_page_loads_attachments(): void
    {
        $vehicle = Vehicle::first();

        // Create service with attachment
        $this->post(route('vehicle_service.upsert'), [
            'vehicle' => $vehicle->id,
            'service' => 1,
            'date' => '2026-01-01',
            'to_date' => '2027-01-01',
            'reminder_months' => 2,
            'service_amount' => 1000,
            'name' => ['Test Item'],
            'amount' => [500],
            'warranty_expiry_date' => [null],
            'warranty_term' => [null],
            'attachment' => [
                UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            ],
        ]);

        $service = VehicleService::latest()->first();

        $response = $this->get(route('vehicle_service.edit', ['service' => $service]));
        $response->assertStatus(200);
        $response->assertSee($service->attachments->first()->src);
    }
}
