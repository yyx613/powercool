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
            'generated_service_form' => false,
            'generated_quotation' => false,
            'generated_cash_sale' => false,
            'generated_invoice' => false,
        ], $overrides));

        return $sf;
    }

    // ========== Part 1: Generated flags tracking ==========

    public function test_service_form_has_generated_flag_columns()
    {
        $sf = $this->createServiceForm();

        $this->assertFalse((bool) $sf->generated_service_form);
        $this->assertFalse((bool) $sf->generated_quotation);
        $this->assertFalse((bool) $sf->generated_cash_sale);
        $this->assertFalse((bool) $sf->generated_invoice);
    }

    public function test_pdf_endpoint_sets_generated_service_form_flag()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->get("/service-form/pdf/{$encryptedId}");

        $response->assertStatus(200);
        $sf->refresh();
        $this->assertTrue((bool) $sf->generated_service_form);
        $this->assertFalse((bool) $sf->generated_quotation);
        $this->assertFalse((bool) $sf->generated_cash_sale);
        $this->assertFalse((bool) $sf->generated_invoice);
    }

    public function test_quotation_pdf_sets_generated_quotation_flag()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->get("/service-form/quotation-pdf/{$encryptedId}");

        $response->assertStatus(200);
        $sf->refresh();
        $this->assertTrue((bool) $sf->generated_quotation);
    }

    public function test_cash_sale_pdf_sets_generated_cash_sale_flag()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->get("/service-form/cash-sale-pdf/{$encryptedId}");

        $response->assertStatus(200);
        $sf->refresh();
        $this->assertTrue((bool) $sf->generated_cash_sale);
    }

    public function test_invoice_pdf_sets_generated_invoice_flag()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm();
        $encryptedId = Crypt::encrypt($sf->id);

        $response = $this->actingAs($user)->get("/service-form/invoice-pdf/{$encryptedId}");

        $response->assertStatus(200);
        $sf->refresh();
        $this->assertTrue((bool) $sf->generated_invoice);
    }

    // ========== Part 1: getData returns flags ==========

    public function test_get_data_returns_generated_flags()
    {
        $user = $this->getOrCreateUser();
        $sf = $this->createServiceForm([
            'generated_service_form' => true,
            'generated_invoice' => true,
        ]);

        $response = $this->actingAs($user)->get('/service-form/get-data?page=1');

        $response->assertStatus(200);
        $json = $response->json();

        // Find our service form in the response data
        $found = collect($json['data'])->first(function ($item) use ($sf) {
            return $item['sku'] === $sf->sku;
        });

        $this->assertNotNull($found, 'Service form not found in getData response');
        $this->assertTrue($found['generated_service_form']);
        $this->assertFalse($found['generated_quotation']);
        $this->assertFalse($found['generated_cash_sale']);
        $this->assertTrue($found['generated_invoice']);
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
        $sf = $this->createServiceForm(['generated_invoice' => false]);
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
            'generated_invoice' => true,
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
        $sf = $this->createServiceForm(['generated_invoice' => true]);
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
