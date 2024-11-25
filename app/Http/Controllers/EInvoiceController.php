<?php

namespace App\Http\Controllers;

use App\Mail\EInvoiceEmail;
use App\Models\ConsolidatedEInvoice;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\DebitNote;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\EInvoice;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleProduct;
use BaconQrCode\Encoder\QrCode as EncoderQrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use App\Services\EInvoiceXmlGenerator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use function PHPUnit\Framework\isEmpty;

class EInvoiceController extends Controller
{
    protected $endpoint;
    protected $powerCoolId;
    protected $powerCoolSecret;
    protected $hitenId;
    protected $hitenSecret;
    protected $msic = '01111';
    protected $xmlGenerator;
    protected $accessTokenPowerCool;
    protected $accessTokenHiten;
    protected $powerCoolTin;
    protected $hitenTin;


    public function __construct()
    {
        $this->powerCoolId = config('e-invoices.powercool_client_id');
        $this->powerCoolSecret = config('e-invoices.powercool_client_secret');
        $this->hitenId = config('e-invoices.hiten_client_id');
        $this->hitenSecret = config('e-invoices.hiten_client_secret');
        $this->endpoint = 'https://preprod-api.myinvois.hasil.gov.my';
        $this->xmlGenerator = new EInvoiceXmlGenerator();
        $this->accessTokenPowerCool = $this->login('powercool')->getData()->access_token ?? null;
        $this->accessTokenHiten = $this->accessTokenPowerCool;
        // $this->accessTokenHiten = $this->login('hiten')->getData()->access_token ?? null;

        $this->powerCoolTin = "IG26663185010";
        $this->hitenTin = "IG26663185010";
    }

    public function login($company)
    {
        try {
            $path = "/connect/token";
            $url = $this->endpoint . $path;

            $clientId = $company == "powercool" ? $this->powerCoolId : $this->hitenId; 
            $clientSecret = $company == "powercool" ? $this->powerCoolSecret : $this->hitenSecret;

            $response = Http::asForm()->post($url, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'client_credentials',
                'scope' => 'InvoicingAPI',
            ]);

            if ($response->successful()) {
                $accessToken = $response->json()['access_token'];
                $expiresIn = $response->json()['expires_in'];

                return response()->json([
                    'access_token' => $accessToken,
                    'expires_in' => $expiresIn,
                ]);
            } else {
                return response()->json([
                    'error' => 'Login failed',
                    'message' => $response->body(),
                ], $response->status());
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred during login',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function validateTIN($tin, $idType, $idValue, $company)
    {
        try {
            $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/taxpayer/validate/{$tin}?idType={$idType}&idValue={$idValue}";

            $headers = [
                'Accept' => 'application/json',
                'Accept-Language' => 'en',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' .$company == "powercool" ? $this->accessTokenPowerCool : $this->accessTokenHiten,
            ];

            $response = Http::withHeaders($headers)->get($url);

            if ($response->successful()) {
                return response()->json([
                    'message' => 'TIN validation successful',
                    'data' => $response->json(),
                ]);
            } 
            else {
                return response()->json([
                    'error' => 'TIN validation failed',
                    'message' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred during TIN validation',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function submit(Request $request){
        $request->validate([
            'invoices' => 'required|array',
            'invoices.*.id' => 'required|integer',
        ]);
        $selectedInvoices = $request->input('invoices');
        $company = $request->input('company');

        $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/documentsubmissions";

        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => 'en',
            'Content-Type' => 'application/json', 
            'Authorization' => 'Bearer ' . $company == 'powercool' ? $this->accessTokenPowerCool : $this->accessTokenHiten, 
        ];
        $documents = [];
        foreach ($selectedInvoices as $invoice) {
            $invoice = Invoice::find($invoice['id']);
            $document = $this->xmlGenerator->generateXml($invoice['id'], $company == 'powercool' ? $this->powerCoolTin : $this->hitenTin);
            $documents[] = [
                'format' => 'XML',
                'document' => base64_encode($document),
                'documentHash' => hash('sha256', $document),
                'codeNumber' => $invoice->sku,
            ];
        }
        $payload = [
            'documents' => $documents,
        ];
        
        $response = Http::withHeaders($headers)->post($url, $payload);
        if ($response->successful()) {
            DB::beginTransaction();
            try {
                $acceptedDocuments = $response->json()['acceptedDocuments'] ?? [];
                $rejectedDocuments = $response->json()['rejectedDocuments'] ?? [];
                $errorDetails = [];
                $successfulDocuments = [];
                foreach ($acceptedDocuments as $document) {
                    $uuid = $document['uuid'];
                    $invoiceCodeNumber = $document['invoiceCodeNumber'];
                    
                    $documentDetails = $this->getDocumentDetails($uuid, $company);
                    if (isset($documentDetails['error'])) {
                        $errorDetails[] = [
                            'invoiceCodeNumber' => $invoiceCodeNumber,
                            'error' => $documentDetails['error'],
                        ];
                        continue;
                    }

                    $invoice = Invoice::where('sku', $invoiceCodeNumber)->first();

                    if (!$invoice->einvoice) {
                        $invoice->einvoice()->create([
                            'uuid' => $uuid,
                            'status' => 'Valid',
                            'submission_date' => Carbon::now()
                        ]);
                    } else {
                        $invoice->einvoice->update([
                            'uuid' => $uuid,
                            'status' => 'Valid',
                            'submission_date' => Carbon::now()
                        ]);
                    }
                    $successfulDocuments[] = $invoiceCodeNumber;

                    if (isset($documentDetails['uuid']) && isset($documentDetails['longId'])) {
                        $generatedPdf = $this->generateAndSaveEInvoicePdf($documentDetails, $invoiceCodeNumber);
                    }
                }

                if (!empty($rejectedDocuments)) {
                    $errorDetails = [];
                    foreach ($rejectedDocuments as $rejectedDoc) {
                        $errorDetails[] = [
                            'invoiceCodeNumber' => $rejectedDoc['invoiceCodeNumber'],
                            'error_code' => $rejectedDoc['error']['code'],
                            'error_message' => $rejectedDoc['error']['message'],
                            'error_target' => $rejectedDoc['error']['target'],
                            'property_path' => $rejectedDoc['error']['propertyPath'],
                            'details' => array_map(function ($detail) {
                                return [
                                    'code' => $detail['code'],
                                    'message' => $detail['message'],
                                    'target' => $detail['target'],
                                    'propertyPath' => $detail['propertyPath'],
                                ];
                            }, $rejectedDoc['error']['details'] ?? []),
                        ];
                    }
                    DB::rollBack();
                    dd($response->json());
                    return response()->json([
                        'error' => 'Some documents were rejected',
                        'rejectedDocuments' => $errorDetails,
                    ], 400);
                }

                DB::commit();

                return response()->json([
                    'message' => 'Document submission completed',
                    'successfulDocuments' => $successfulDocuments,
                    'errorDetails' => $errorDetails,
                ]);

            } catch (\Throwable $th) {
                DB::rollBack();
                dd([$response->body(), $th]);
            }
        } else {
            return response()->json([
                'error' => 'Document submission failed',
                'message' => $response->body(),
            ], $response->status());
        }
        
    }
    public function submitConsolidated(Request $request)
    {
        $request->validate([
            'invoices' => 'required|array',
            'invoices.*.id' => 'required|integer',
        ]);
        $invoices = $request->input('invoices');
        $company = $request->input('company');

        $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/documentsubmissions";
        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => 'en',
            'Content-Type' => 'application/json', 
            'Authorization' => 'Bearer ' . ($company == 'powercool' ? $this->accessTokenPowerCool : $this->accessTokenHiten), 
        ];
        $documents = [];

        $sku = (new ConsolidatedEInvoice)->generateSku();

        DB::beginTransaction();
        
        try {
            $consolidated = ConsolidatedEInvoice::create([
                'sku' => $sku
            ]);
            
            $document = $this->xmlGenerator->generateConsolidatedXml($invoices, $consolidated, $company == 'powercool' ? $this->powerCoolTin : $this->hitenTin);
            
            $documents[] = [
                'format' => 'XML',
                'document' => base64_encode($document),
                'documentHash' => hash('sha256', $document),
                'codeNumber' => $sku,
            ];
            
            $payload = [
                'documents' => $documents,
            ];

            $response = Http::withHeaders($headers)->post($url, $payload);
            if ($response->successful()) {
                
                $consolidated->invoices()->sync(array_column($invoices, 'id'));
                $acceptedDocuments = $response->json()['acceptedDocuments'] ?? [];
                $rejectedDocuments = $response->json()['rejectedDocuments'] ?? [];
        
                foreach ($acceptedDocuments as $document) {
                    $uuid = $document['uuid'];
                    
                    $invoiceCodeNumber = $document['invoiceCodeNumber'];
                    $documentDetails = $this->getDocumentDetails($uuid, $company);
                    
                    if (isset($documentDetails['uuid']) && isset($documentDetails['longId'])) {
                        $consolidated->update(['uuid' => $uuid, 'status' => 'valid']);
                        $generatedPdf = $this->generateAndSaveConsolidatedPdf($documentDetails, $invoiceCodeNumber);
                    } else {
                        DB::rollBack();
                        return $documentDetails;
                    }
                }
        
                if (!empty($rejectedDocuments)) {
                    $errorDetails = [];
                    foreach ($rejectedDocuments as $rejectedDoc) {
                        $errorDetails[] = [
                            'invoiceCodeNumber' => $rejectedDoc['invoiceCodeNumber'],
                            'error_code' => $rejectedDoc['error']['code'],
                            'error_message' => $rejectedDoc['error']['message'],
                            'error_target' => $rejectedDoc['error']['target'],
                            'property_path' => $rejectedDoc['error']['propertyPath'],
                            'details' => array_map(function ($detail) {
                                return [
                                    'code' => $detail['code'],
                                    'message' => $detail['message'],
                                    'target' => $detail['target'],
                                    'propertyPath' => $detail['propertyPath'],
                                ];
                            }, $rejectedDoc['error']['details'] ?? []),
                        ];
                    }
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Some documents were rejected',
                        'rejectedDocuments' => $errorDetails,
                    ], 400);
                }
        
                DB::commit();
        
                return response()->json([
                    'message' => 'Document submission successful',
                    'acceptedDocuments' => $acceptedDocuments,
                ]);
        
            } else {
                DB::rollBack();
                return response()->json([
                    'error' => 'Document submission failed',
                    'message' => $response->body(),
                ], $response->status());
            }
            
        } catch (\Throwable $th) {
            DB::rollBack();
            dd([ $th]);
        }
    }
    
    public function getDocumentDetails($uuid,$company)
    {
        try {
            $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/documents/{$uuid}/details";

            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $company == 'powercool' ? $this->accessTokenPowerCool : $this->accessTokenHiten,
            ];

            $maxRetries = 10; 
            $retryCount = 0;
            $delay = 2; 
            while ($retryCount < $maxRetries) {
                $response = Http::withHeaders($headers)->get($url);
                if ($response->successful()) {
                    $validationResults = $response->json()['validationResults'] ?? null;
    
                    if ($validationResults) {
                        if ($validationResults['status'] === 'Invalid') {
                            $validationSteps = $validationResults['validationSteps'] ?? [];
                            foreach ($validationSteps as $step) {
                                if ($step['status'] === 'Invalid') {
                                    $error = $step['error']['innerError'][0]['error'] ?? null;
                                    if ($error) {
                                        return ['error' => $error];
                                    }
                                }
                            }
                        }
                    }
    
                    $longId = $response->json()['longId'] ?? null;
                    if (!empty($longId)) {
                        return [
                            'uuid' => $response->json()['uuid'] ?? null,
                            'longId' => $longId,
                        ];
                    }
                } else {
                    return ['error' => $response->body(), 'message' => $response->body()];
                }
    
                $retryCount++;
                sleep($delay); 
            }
        } catch (\Throwable $th) {
            dd($th);
        }

        return ['error' => 'Failed to retrieve longId after multiple attempts'];
    }

    public function generateAndSaveEInvoicePdf($documentDetails, $invoiceCodeNumber)
    {
        try {
            $uuid = $documentDetails['uuid'];
            $longId = $documentDetails['longId'];

            $invoice = Invoice::where('sku', $invoiceCodeNumber)->first();
            $do_ids = DeliveryOrder::where('invoice_id', $invoice->id)->pluck('id');
            $do_sku = DeliveryOrder::whereIn('id', $do_ids)->pluck('sku')->toArray();

            $delivery = DeliveryOrder::where('invoice_id', $invoice->id)->first();
            $firstDeliveryProduct = $delivery->products()->first();
            $deliveryProduct = $delivery->products()->get();

            $saleProduct = $firstDeliveryProduct->saleProduct;
            $sale = $saleProduct->sale;

            $customer = $sale->customer;
            $validationLink = $this->generateValidationLink($uuid,$longId);

            $pdf = Pdf::loadView('invoice.pdf.' . $invoice->company. '_inv_pdf', [
                'date' => now()->format('d/m/Y'),
                'sku' => $invoiceCodeNumber,
                'do_sku' => join(', ', $do_sku),
                'dos' => '$dos',
                'do_products' => $deliveryProduct,
                'customer' => $customer,
                'billing_address' => (new CustomerLocation)->defaultBillingAddress($customer->id),
                'terms' => '', 
                'validationLink' => $validationLink,
                'delivery_address' => CustomerLocation::find($sale->delivery_address_id)
            ]);
            
            $pdf->setPaper('A4', 'letter');
            
            $content = $pdf->download()->getOriginalContent();
            
            $e = Storage::put('public/e-invoices/pdf/e-invoices/e_invoice_'.$uuid.'.pdf', $content);
            return $e;
        } catch (\Throwable $th) {
            dd($th);
            return false;
        }
    }

    private function getPdfType(Collection $sale_products): string
    {
        $is_hi_ten = false;

        for ($i = 0; $i < count($sale_products); $i++) {
            $product = $sale_products[$i]->product;

            if ($product->type == Product::TYPE_PRODUCT) {
                $is_hi_ten = true;
                break;
            }
        }
        return $is_hi_ten ? 'hi_ten' : 'powercool';
    }

   

    public function generateAndSaveConsolidatedPdf($documentDetails, $consolidatedSku)
    {
        try {
            $uuid = $documentDetails['uuid'];
            $longId = $documentDetails['longId'];

            $consolidatedInvoice = ConsolidatedEInvoice::where('sku', $consolidatedSku)->first();

            $invoices = $consolidatedInvoice->invoices;
            $deliveryProducts = collect();

            foreach ($invoices as $invoice) {
                $delivery = DeliveryOrder::where('invoice_id', $invoice->id)->first();
                if ($delivery) {
                    $deliveryProducts = $deliveryProducts->merge($delivery->products);
                }
            }

            $validationLink = $this->generateValidationLink($uuid, $longId);

            $do_skus = $invoices->map(function($invoice) {
                return DeliveryOrder::where('invoice_id', $invoice->id)->first()->sku ?? '';
            })->filter()->toArray();
            $do_ids = $invoices->map(function($invoice) {
                return DeliveryOrder::where('invoice_id', $invoice->id)->first()->id ?? '';
            })->filter()->toArray();
            $sale_products = SaleProduct::whereIn('id', DeliveryOrderProduct::whereIn('delivery_order_id', $do_ids)->pluck('sale_product_id'))->get();

            $pdf = Pdf::loadView('invoice.pdf.consolidated.'.$this->getPdfType($sale_products).'_inv_pdf', [
                'date' => now()->format('d/m/Y'),
                'sku' => $consolidatedSku,
                'do_sku' => implode(', ', $do_skus),
                'dos' => $invoices,
                'do_products' => $deliveryProducts,
                'terms' => '', 
                'validationLink' => $validationLink,
            ]);

            $pdf->setPaper('A4', 'letter');
            
            $content = $pdf->download()->getOriginalContent();
            Storage::put('public/e-invoices/pdf/consolidated/' . 'consolidated_e_invoice_'.$uuid . '.pdf', $content);

            return true;
        } catch (\Throwable $th) {
            dd($th);
            return false;
        }
    }

    public function sendEmail(Request $req){
        $id = $req->input('id');
        $type = $req->input('type');
        if($type == 'eInvoice'){
            $einvoice = EInvoice::find($id);
            $invoice = $einvoice->invoice;
            $deliveryOrder = $invoice->deliveryOrders->first();
            $customer = Customer::findOrFail($deliveryOrder->customer_id);
            $company = $invoice->company == 'powercool' ? 'PowerCool' : 'Hi-Ten';
            $path = public_path('storage/e-invoices/pdf/e-invoices/' . 'e_invoice_' . $einvoice->uuid . '.pdf');
            Mail::to($customer->email)->send(new EInvoiceEmail($customer, $einvoice, $path, $company));
        }
        else if($type == 'credit'){
            $creditNote = CreditNote::find($id);
            $customer = $creditNote->eInvoices()
            ->with('invoice.deliveryOrders.customer')
            ->get()
            ->pluck('invoice.deliveryOrders')
            ->flatten()
            ->pluck('customer')
            ->first();
            $company = $creditNote->einvoices()->first()->invoice->company == 'powercool' ? 'PowerCool' : 'Hi-Ten';
            $path = public_path('storage/e-invoices/pdf/credit_note/' . 'credit_note_' . $creditNote->uuid . '.pdf');
            Mail::to($customer->email)->send(new EInvoiceEmail($customer, $creditNote, $path, $company));
        }
        else if($type == 'debit'){
            $debitNote = DebitNote::find($id);
            $customer = $debitNote->eInvoices()
            ->with('invoice.deliveryOrders.customer')
            ->get()
            ->pluck('invoice.deliveryOrders')
            ->flatten()
            ->pluck('customer')
            ->first();
            $company = $debitNote->einvoices()->first()->invoice->company == 'powercool' ? 'PowerCool' : 'Hi-Ten';
            $path = public_path('storage/e-invoices/pdf/debit_note/' . 'debit_note_' . $debitNote->uuid . '.pdf');
            Mail::to($customer->email)->send(new EInvoiceEmail($customer, $debitNote, $path, $company));
        }

        return response()->json(['message' => 'email sent']);
    }
    

    public function generateValidationLink($uuid, $longId)
    {
        $envbaseurl = 'https://preprod.myinvois.hasil.gov.my';
        $validationLink = "{$envbaseurl}/{$uuid}/share/{$longId}";
        return $validationLink;
    }

    public function generateQrCode($uuid, $longId)
    {
        $qrCode = QrCode::size(100)->generate($this->generateValidationLink($uuid,$longId));
        return $qrCode;
    }
  
    public function download(Request $req)
    {
        $uuid = $req->input('uuid');
        $type = $req->input('type');
        if($type == 'consolidated'){
            $path = '/public/e-invoices/pdf/consolidated/consolidated_e_invoice_' . $uuid . '.pdf';

            if (Storage::exists($path)) {
                return Storage::download($path);
            } else {
                return response()->json(['error' => '文件未找到'], 404);
            }
        }else if($type == 'eInvoice'){
            $path = '/public/e-invoices/pdf/e-invoices/e_invoice_' . $uuid . '.pdf';

            if (Storage::exists($path)) {
                return Storage::download($path);
            } else {
                return response()->json(['error' => '文件未找到'], 404);
            }
        }else if($type == 'credit'){
            $path = '/public/e-invoices/pdf/credit_note/credit_note_' . $uuid . '.pdf';

            if (Storage::exists($path)) {
                return Storage::download($path);
            } else {
                return response()->json(['error' => '文件未找到'], 404);
            }
        }else if($type == 'debit'){
            $path = '/public/e-invoices/pdf/debit_note/debit_note_' . $uuid . '.pdf';

            if (Storage::exists($path)) {
                return Storage::download($path);
            } else {
                return response()->json(['error' => '文件未找到'], 404);
            }
        }
    }
    
    public function submitNote(Request $request)
    {
        $noteType = Session::get('note_type');
        $type = Session::get('invoice_type');
        $company = Session::get('company');
        $invoices = $request->input('invoices');
        $totals = [];
        $qtyDifferences = [];
        $eInvoiceIds = [];
        $totalsModified = 0;

        DB::beginTransaction();

        try {
            foreach ($invoices as $invoice) {
                $invoiceUuid = $invoice['invoice_uuid'];

                if ($type == 'eInvoice') {
                    $eInvoice = EInvoice::where('uuid', $invoiceUuid)->first();
                } else {
                    $eInvoice = ConsolidatedEInvoice::where('uuid', $invoiceUuid)->first();
                }

                if ($eInvoice && !in_array($eInvoice->id, $eInvoiceIds)) {
                    $eInvoiceIds[] = $eInvoice->id;
                }

                foreach ($invoice['items'] as $item) {
                    $saleProduct = SaleProduct::find($item['product_id']);

                    if (!$saleProduct) {
                        continue;
                    }

                    $saleId = $saleProduct->sale->id;
                    $amount = $item['qty'] * $item['price'];

                    if (!isset($totals[$saleId])) {
                        $totals[$saleId] = 0;
                    }
                    $totals[$saleId] += $amount;

                    $qtyDifference = abs($saleProduct->qty - $item['qty']);
                    $priceDifference = abs($saleProduct->unit_price - $item['price']);

                    if ($qtyDifference != 0 || $priceDifference != 0) {
                        $totalsModified += $qtyDifference * $item['price'];
                        $qtyDifferences[] = [
                            'id' => $item['product_id'],
                            'diff' => $qtyDifference == 0 ? $saleProduct->qty : $qtyDifference,
                            'price' => $item['price']
                        ];
                    }

                    $saleProduct->update([
                        'qty' => $item['qty'],
                        'unit_price' => $item['price']
                    ]);

                    $customer = $saleProduct->sale->customer;
                }
            }

            if (empty($qtyDifferences)) {
                return response()->json([
                    'message' => 'Nothing to Change!',
                ]);
            }

            foreach ($totals as $saleId => $totalAmount) {
                Sale::find($saleId)->update(['payment_amount' => $totalAmount]);
            }

            if ($noteType == 'credit') {
                $sku = (new CreditNote)->generateSku();
                $note = CreditNote::create(['sku' => $sku]);
            } else {
                $sku = (new DebitNote)->generateSku();
                $note = DebitNote::create(['sku' => $sku]);
            }

            if ($type == 'eInvoice') {
                $note->eInvoices()->attach($eInvoiceIds);
            } else {
                $note->consolidatedEInvoice()->attach($eInvoiceIds);
            }

            $tin = $company == 'powercool' ? $this->powerCoolTin : $this->hitenTin;
            $document = $this->xmlGenerator->generateNoteXml($eInvoiceIds, $qtyDifferences, $note, $totalsModified, $type, $tin,$customer);
            
            $result = $this->syncNote($document, $note, $qtyDifferences, $company);
            if(!empty($result->original['errorDetails'])){
                DB::rollBack();
            }else{
                DB::commit();
            }
            return $result;
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }


    public function syncNote($document, $note, $qtyDifferences, $company)
    {
        $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/documentsubmissions";
        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => 'en',
            'Content-Type' => 'application/json', 
            'Authorization' => 'Bearer ' . ($company == 'powercool' ? $this->accessTokenPowerCool : $this->accessTokenHiten), 
        ];

        $documents = [];
        $documents[] = [
            'format' => 'XML',
            'document' => base64_encode($document),
            'documentHash' => hash('sha256', $document),
            'codeNumber' => $note->sku,
        ];
        $payload = [
            'documents' => $documents,
        ];

        DB::beginTransaction();

        try {
            $response = Http::withHeaders($headers)->post($url, $payload);

            if ($response->successful()) {
                $acceptedDocuments = $response->json()['acceptedDocuments'] ?? [];
                $rejectedDocuments = $response->json()['rejectedDocuments'] ?? [];
                $errorDetails = [];
                $successfulDocuments = [];

                foreach ($acceptedDocuments as $document) {
                    $uuid = $document['uuid'];
                    $invoiceCodeNumber = $document['invoiceCodeNumber'];
                    $documentDetails = $this->getDocumentDetails($uuid, $company);

                    if (isset($documentDetails['error'])) {
                        $errorDetails[] = [
                            'invoiceCodeNumber' => $invoiceCodeNumber,
                            'error' => $documentDetails['error'],
                        ];
                        continue;
                    }

                    if ($note instanceof CreditNote) {
                        $note = CreditNote::where('sku', $invoiceCodeNumber)->first();
                        if ($note) {
                            $note->update([
                                'uuid' => $uuid,
                                'status' => 'valid'
                            ]);
                        }
                    } else {
                        $note = DebitNote::where('sku', $invoiceCodeNumber)->first();
                        if ($note) {
                            $note->update([
                                'uuid' => $uuid,
                                'status' => 'valid'
                            ]);
                        }
                    }

                    $successfulDocuments[] = $invoiceCodeNumber;

                    if (isset($documentDetails['uuid']) && isset($documentDetails['longId'])) {
                        $this->generateAndSaveNotePdf($documentDetails, $note, $qtyDifferences);
                    }
                }

                
                if (!empty($rejectedDocuments)) {
                    $errorDetails = [];
                    foreach ($rejectedDocuments as $rejectedDoc) {
                        $errorDetails[] = [
                            'invoiceCodeNumber' => $rejectedDoc['invoiceCodeNumber'],
                            'error_code' => $rejectedDoc['error']['code'],
                            'error_message' => $rejectedDoc['error']['message'],
                            'error_target' => $rejectedDoc['error']['target'],
                            'property_path' => $rejectedDoc['error']['propertyPath'],
                            'details' => array_map(function ($detail) {
                                return [
                                    'code' => $detail['code'],
                                    'message' => $detail['message'],
                                    'target' => $detail['target'],
                                    'propertyPath' => $detail['propertyPath'],
                                ];
                            }, $rejectedDoc['error']['details'] ?? []),
                        ];
                    }
                    DB::rollBack();
                    // return response()->json([
                    //     'error' => 'Some documents were rejected',
                    //     'rejectedDocuments' => $errorDetails,
                    // ], 400);
                }else{
                    DB::commit();
                }

                return response()->json([
                    'message' => 'Document submission completed',
                    'successfulDocuments' => $successfulDocuments,
                    'errorDetails' => $errorDetails,
                ]);

            } else {
                DB::rollBack();
                return response()->json([
                    'error' => 'Document submission failed',
                    'message' => $response->body(),
                ], $response->status());
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            dd([$response->body(), $th]);
        }
    }


    public function generateAndSaveNotePdf($documentDetails, $note,$items)
    {
        try {
            $uuid = $documentDetails['uuid'];
            $longId = $documentDetails['longId'];

            $total = 0;
            $productDetails = [];

            foreach ($items as $key => $item) {
                $saleProduct = SaleProduct::find($item['id']);

                if ($saleProduct) {
                    $productDetails[] = [
                        'index' => $key + 1,
                        'model_name' => $saleProduct->product->model_name ?? '',
                        'qty' => $item['diff'],
                        'uom' => $saleProduct->product->uom ?? '',
                        'unit_price' => $item['price'],
                        'subtotal' => $item['diff'] * $item['price'],
                    ];

                    $total += $item['diff'] * $item['price'];
                }
            }
            
            $eInvoices = $note->eInvoices;
            
            if ($eInvoices->isNotEmpty()) {
                $eInvoice = $eInvoices->first();
        
                $invoice = $eInvoice->invoice;
        
                $deliveryOrder = $invoice->deliveryOrders->first();
        
                $customer = Customer::find($deliveryOrder->customer_id);

                $sale = $deliveryOrder->products->first()->saleProduct->sale;
            } else {
                $customer = null;
            }
          
            $saleProductIds = array_column($items, 'id');

            $validationLink = $this->generateValidationLink($uuid,$longId);
            $pdf = Pdf::loadView('invoice.pdf.note.' . $invoice->company . '_inv_pdf', [
                'date' => now()->format('d/m/Y'),
                'sku' => $note->sku,
                'productDetails' => $productDetails,
                'total' => $total,
                'customer' => $customer,
                'billing_address' => (new CustomerLocation)->defaultBillingAddress($customer->id),
                'terms' => '', 
                'type' => $note instanceof CreditNote ? 'CREDIT NOTE' : 'DEBIT NOTE',
                'validationLink' => $validationLink,
                'delivery_address' => CustomerLocation::find($sale->delivery_address_id)
            ]);
            
            $pdf->setPaper('A4', 'letter');
            
            $content = $pdf->download()->getOriginalContent();
            $type = $note instanceof CreditNote ? 'credit_note' : 'debit_note';
            return Storage::put('public/e-invoices/pdf/'.$type.'/'.$type.'_'.$uuid.'.pdf', $content);
        } catch (\Throwable $th) {
            dd($th,$items);
            return false;
        }
    }

    public function toNote(Request $req)
    {
        $type = $req->input('from');
        if($type){
            Session::put('invoice_type',$type);
        }
        $step = 1;
        if ($req->has('invs') || $step == 4) {
            $step = 5;
            $selectedInvoiceIds = explode(',', $req->invs);

            if (!$selectedInvoiceIds || !is_array($selectedInvoiceIds)) {
                return response()->json(['error' => 'Invalid invoice IDs provided'], 400);
            }

            $results = [];
            if (Session::get('invoice_type') == 'eInvoice') {
                foreach ($selectedInvoiceIds as $invoiceId) {
                    $eInvoice = EInvoice::find($invoiceId);
                    $invoice = $eInvoice->invoice;
    
                    if ($invoice) {
                        $delivery = DeliveryOrder::where('invoice_id', $invoice->id)->first();
    
                        if ($delivery) {
                            $invoiceItems = [];
                            foreach ($delivery->products()->get() as $product) {
                                $saleProduct = $product->saleProduct;
                                $invoiceItems[] = [
                                    'product_id' => $product->saleProduct->id,
                                    'name' => $saleProduct->product->model_name,
                                    'qty' => $saleProduct->qty,
                                    'price' => $saleProduct->unit_price,
                                ];
                            }
    
                            $results[] = [
                                'invoice_uuid' => $eInvoice->uuid,
                                'items' => $invoiceItems
                            ];
                        }
                    }
                }
            }else{    
                if (!$selectedInvoiceIds || !is_array($selectedInvoiceIds)) {
                    return response()->json(['error' => 'Invalid invoice IDs provided'], 400);
                }
                
                $results = [];
        
                foreach ($selectedInvoiceIds as $invoiceId) {
                    $eInvoice = ConsolidatedEInvoice::find($invoiceId);
                    $invoices = $eInvoice->invoices;
                    if ($invoices) {
                        foreach ($invoices as $invoice) {
                            $delivery = DeliveryOrder::where('invoice_id', $invoice->id)->first();
        
                            if ($delivery) {
                                $invoiceItems = [];
                                foreach ($delivery->products()->get() as $product) {
                                    $saleProduct = $product->saleProduct;
                                    $invoiceItems[] = [
                                        'product_id' => $product->saleProduct->id,
                                        'name' => $saleProduct->product->model_name,
                                        'qty' => $saleProduct->qty,
                                        'price' => $saleProduct->unit_price,
                                    ];
                                }
        
                                $results[] = [
                                    'invoice_uuid' => $eInvoice->uuid,
                                    'items' => $invoiceItems
                                ];
                            }
                        }
                    }
                }
            }
           
        } else if ($req->has('cus')) {
            $step = 4;
            $customerId = $req->cus;
            $eInvoices = EInvoice::whereHas('invoice.deliveryOrders', function ($query) use ($customerId) {
                $query->where('customer_id', $customerId);
                $company = Session::get('company') == 'powercool' ? 'powercool' : 'hi_ten';
                $query->whereHas('invoice', function($invoiceQuery) use ($company) {
                    $invoiceQuery->where('company', $company);
                });
            })->get();
        }
        else if ($req->has('type')) {
            Session::put('note_type', $req->type);
            if (Session::get('invoice_type') == 'eInvoice') {
                $step = 3;
                $customers = Customer::whereHas('deliveryOrders.invoice', function($query) {
                    $query->whereHas('eInvoice');
                    if (Session::get('company') == 'powercool') {
                        $query->where('company', 'powercool');
                    }else{
                        $query->where('company', 'hi_ten');
                    }
                })->get();
            } else {
                $step = 4;
                $eInvoices = ConsolidatedEInvoice::all(); 
            }
        }
        else if ($req->has('company')) {
            Session::put('company', $req->company);
            $step = 2;
        }
        else {
            $step = 1;
        }

        return view('invoice.convert', [
            'step' => $step,
            'customers' => $customers ?? [],
            'eInvoices' => $eInvoices ?? [],
            'results' => $results ?? []
        ]);
    }

    public function cancelEInvoice(Request $req){
        $uuid = $req->input('uuid');
        $reason = $req->input('reason');
        $eInvoice = EInvoice::where('uuid', $uuid)->first();

        $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/documents/state/{$uuid}/state";

        $body = [
            'status' => 'cancelled',
            'reason' => $reason,
        ];
        DB::beginTransaction();
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $eInvoice->invoice->company == "powercool" ? $this->accessTokenPowerCool : $this->accessTokenHiten,
                'Content-Type' => 'application/json',
            ])->put($url, $body);
        
            if ($response->successful()) {
                $data = $response->json();
                $eInvoice->update([
                    'status' => $data['status']
                ]);
                DB::commit();
                return [
                    'uuid' => $data['uuid'] ?? null,
                    'status' => $data['status'] ?? 'Unknown',
                ];
            } else {
                return [
                    'error' => $response->json()['error']['message'] ?? 'Failed to cancel the document',
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }

    public function resubmitEInvoice(Request $request){
        $request->validate([
            'uuid' => 'required'
        ]);

        $eInvoice = EInvoice::where('uuid',$request->input('uuid'))->first();
        $invoice = $eInvoice->invoice;
        $company = $invoice->company;
        
        $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/documentsubmissions";

        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => 'en',
            'Content-Type' => 'application/json', 
            'Authorization' => 'Bearer ' . $company == 'powercool' ? $this->accessTokenPowerCool : $this->accessTokenHiten, 
        ];

        $documents = [];

        $document = $this->xmlGenerator->generateXml($invoice->id, $company == 'powercool' ? $this->powerCoolTin : $this->hitenTin);

        $documents[] = [
            'format' => 'XML',
            'document' => base64_encode($document),
            'documentHash' => hash('sha256', $document),
            'codeNumber' => $invoice->sku,
        ];

        $payload = [
            'documents' => $documents,
        ];
        
        $response = Http::withHeaders($headers)->post($url, $payload);
        if ($response->successful()) {
            DB::beginTransaction();
            try {
                $acceptedDocuments = $response->json()['acceptedDocuments'] ?? [];
                $rejectedDocuments = $response->json()['rejectedDocuments'] ?? [];
                $errorDetails = [];
                $successfulDocuments = [];
                foreach ($acceptedDocuments as $document) {
                    $uuid = $document['uuid'];
                    $invoiceCodeNumber = $document['invoiceCodeNumber'];
                    
                    $documentDetails = $this->getDocumentDetails($uuid, $company);
                    if (isset($documentDetails['error'])) {
                        $errorDetails[] = [
                            'invoiceCodeNumber' => $invoiceCodeNumber,
                            'error' => $documentDetails['error'],
                        ];
                        continue;
                    }

                    $invoice = Invoice::where('sku', $invoiceCodeNumber)->first();

                    if (!$invoice->einvoice) {
                        $invoice->einvoice()->create([
                            'uuid' => $uuid,
                            'status' => 'Valid',
                            'submission_date' => Carbon::now()
                        ]);
                    } else {
                        $invoice->einvoice->update([
                            'uuid' => $uuid,
                            'status' => 'Valid',
                            'submission_date' => Carbon::now()
                        ]);
                    }

                    $successfulDocuments[] = $invoiceCodeNumber;

                    if (isset($documentDetails['uuid']) && isset($documentDetails['longId'])) {
                        $generatedPdf = $this->generateAndSaveEInvoicePdf($documentDetails, $invoiceCodeNumber);
                    }
                }

                if (!empty($rejectedDocuments)) {
                    $errorDetails = [];
                    foreach ($rejectedDocuments as $rejectedDoc) {
                        $errorDetails[] = [
                            'invoiceCodeNumber' => $rejectedDoc['invoiceCodeNumber'],
                            'error_code' => $rejectedDoc['error']['code'],
                            'error_message' => $rejectedDoc['error']['message'],
                            'error_target' => $rejectedDoc['error']['target'],
                            'property_path' => $rejectedDoc['error']['propertyPath'],
                            'details' => array_map(function ($detail) {
                                return [
                                    'code' => $detail['code'],
                                    'message' => $detail['message'],
                                    'target' => $detail['target'],
                                    'propertyPath' => $detail['propertyPath'],
                                ];
                            }, $rejectedDoc['error']['details'] ?? []),
                        ];
                    }
                    DB::rollBack();
                    dd($response->json());
                    return response()->json([
                        'error' => 'Some documents were rejected',
                        'rejectedDocuments' => $errorDetails,
                    ], 400);
                }

                DB::commit();

                return response()->json([
                    'message' => 'Document submission completed',
                    'successfulDocuments' => $successfulDocuments,
                    'errorDetails' => $errorDetails,
                ]);

            } catch (\Throwable $th) {
                DB::rollBack();
                dd([$response->body(), $th]);
            }
        } else {
            return response()->json([
                'error' => 'Document submission failed',
                'message' => $response->body(),
            ], $response->status());
        }
    }
    
}
