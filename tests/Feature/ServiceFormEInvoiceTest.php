<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\EInvoice;
use App\Models\ServiceForm;
use App\Models\ServiceFormProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ServiceFormEInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    private function getOrCreateUser(): User
    {
        $user = User::first();
        if ($user) {
            return $user;
        }

        return User::create([
            'name' => 'Test Admin',
            'email' => 'test_sf_einv@test.com',
            'password' => Hash::make('password'),
            'sku' => 'UTEST'.uniqid(),
        ]);
    }

    private function getOrCreateCustomer(): Customer
    {
        $customer = Customer::first();
        if ($customer) {
            return $customer;
        }

        return Customer::create([
            'name' => 'Test Customer',
            'phone' => '0123456789',
            'sku' => 'CTEST'.uniqid(),
            'company_group' => 1,
        ]);
    }

    private function createServiceForm(array $overrides = []): ServiceForm
    {
        $customer = $this->getOrCreateCustomer();

        $sf = ServiceForm::create(array_merge([
            'sku' => 'SF-TEST-'.uniqid(),
            'date' => now(),
            'customer_id' => $customer->id,
        ], $overrides));

        return $sf;
    }

    // ========== Part 1: Generated flags tracking ==========

    public function test_service_form_has_document_sku_columns()
    {
        $sf = $this->createServiceForm();

        $this->assertNull($sf->sr_sku);
        $this->assertNull($sf->srq_sku);
        $this->assertNull($sf->srcs_sku);
        $this->assertNull($sf->sri_sku);
        $this->assertFalse($sf->generated_service_form);
        $this->assertFalse($sf->generated_quotation);
        $this->assertFalse($sf->generated_cash_sale);
        $this->assertFalse($sf->generated_invoice);
    }

    public function test_pdf_endpoint_assigns_sr_sku()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->get("/service-form/pdf/{$encryptedId}");

        $response->assertStatus(200);
        $sf->refresh();
        $this->assertNotNull($sf->sr_sku);
        $this->assertStringContainsString('SR', $sf->sr_sku);
        $this->assertNull($sf->srq_sku);
        $this->assertNull($sf->srcs_sku);
        $this->assertNull($sf->sri_sku);
    }

    public function test_pdf_endpoint_reuses_existing_sr_sku()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $this->actingAs($user)->get("/service-form/pdf/{$encryptedId}");
        $sf->refresh();
        $firstSku = $sf->sr_sku;

        $this->actingAs($user)->get("/service-form/pdf/{$encryptedId}");
        $sf->refresh();
        $this->assertEquals($firstSku, $sf->sr_sku);
    }

    public function test_quotation_pdf_assigns_srq_sku()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->get("/service-form/quotation-pdf/{$encryptedId}");

        $response->assertStatus(200);
        $sf->refresh();
        $this->assertNotNull($sf->srq_sku);
        $this->assertStringContainsString('SRQ', $sf->srq_sku);
    }

    public function test_cash_sale_pdf_assigns_srcs_sku()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->get("/service-form/cash-sale-pdf/{$encryptedId}");

        $response->assertStatus(200);
        $sf->refresh();
        $this->assertNotNull($sf->srcs_sku);
        $this->assertStringContainsString('SRCS', $sf->srcs_sku);
    }

    public function test_invoice_pdf_assigns_sri_sku()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->get("/service-form/invoice-pdf/{$encryptedId}");

        $response->assertStatus(200);
        $sf->refresh();
        $this->assertNotNull($sf->sri_sku);
        $this->assertStringContainsString('SRI', $sf->sri_sku);
    }

    // ========== Part 1: getData returns flags ==========

    public function test_get_data_returns_document_skus()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm([
            'sr_sku' => 'PSR-26/000001',
            'sri_sku' => 'PSRI-26/000001',
        ]);

        $response = $this->actingAs($user)->get('/service-form/get-data?page=1');

        $response->assertStatus(200);
        $json = $response->json();

        // Find our service form in the response data
        $found = collect($json['data'])->first(function ($item) use ($sf) {
            return $item['sku'] === $sf->sku;
        });

        $this->assertNotNull($found, 'Service form not found in getData response');
        $this->assertEquals('PSR-26/000001', $found['generated_service_form']);
        $this->assertNull($found['generated_quotation']);
        $this->assertNull($found['generated_cash_sale']);
        $this->assertEquals('PSRI-26/000001', $found['generated_invoice']);
    }

    // ========== Part 2: E-Invoice submission ==========

    public function test_einvoice_relationship_exists_on_service_form()
    {
        $sf = $this->createServiceForm();

        $this->assertNull($sf->einvoice);
    }

    public function test_submit_einvoice_requires_generated_invoice()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->postJson('/service-form/submit-e-invoice', [
            'service_form_id' => $encryptedId,
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'Please generate the Invoice PDF first before submitting e-invoice.']);
    }

    public function test_submit_einvoice_requires_customer()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm([
            'sri_sku' => 'PSRI-26/000099',
            'customer_id' => null,
        ]);
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->postJson('/service-form/submit-e-invoice', [
            'service_form_id' => $encryptedId,
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'Service form has no customer assigned.']);
    }

    public function test_submit_einvoice_rejects_duplicate_submission()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm(['sri_sku' => 'PSRI-26/000098']);
        $encryptedId = Crypt::encrypt($sf->id);

        // Create an existing e-invoice for this service form
        $sf->einvoice()->create([
            'uuid' => 'test-uuid-'.uniqid(),
            'longId' => 'test-long-id',
            'status' => 'Valid',
            'submission_date' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/service-form/submit-e-invoice', [
            'service_form_id' => $encryptedId,
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'E-Invoice has already been submitted for this service form.']);
    }
}
