<?php

namespace App\Http\Controllers;

use App\Mail\EInvoiceEmail;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\Sale;
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

class EInvoiceController extends Controller
{
    protected $endpoint;
    protected $clientID;
    protected $clientSecret;
    protected $msic = '01111';

    public function __construct()
    {
        $this->clientID = config('e-invoices.client_id');
        $this->clientSecret = config('e-invoices.client_secret');
        $this->endpoint = 'https://preprod-api.myinvois.hasil.gov.my';
        // $this->platform = Platform::where('name','Shopee')->first();
        // $this->accessToken = PlatformTokens::where('platform_id',$this->platform->id)->first()->access_token;
    }

    public function testSubmitDocument(Request $request)
    {
        $request->validate([
            'documents' => 'required|array',
        ]);

        $documents = $request->input('documents');
        $acceptedDocuments = [];

        // Generate a dynamic list of accepted documents
        foreach ($documents as $document) {
            $acceptedDocuments[] = [
                'uuid' => Str::random(26),  // Randomly generate UUID for each document
                'invoiceCodeNumber' => $document['codeNumber'] ?? 'INV' . rand(10000, 99999),
            ];
        }

        // Mock response data
        $mockResponse = [
            'submission_id' => Str::uuid(),
            'acceptedDocuments' => $acceptedDocuments,
            'rejectedDocuments' => [],
        ];

        return response()->json($mockResponse, 200);
    }

    public function testGetDocumentDetails($uuid)
    {
        $mockResponse = [
            'uuid' => $uuid,
            'longId' => 'LONG-' . strtoupper(Str::random(16)),
            'details' => 'This is a mock document detail response.',
        ];
        return $mockResponse;
    }

    public function login(){
        $path = "/connect/token";
        $url = $this->endpoint.$path;
        // 准备请求体参数
        $clientId = $this->clientID; // 替换为实际的 client_id
        $clientSecret = $this->clientSecret; // 替换为实际的 client_secret
        // 发起 POST 请求
        $response = Http::asForm()->post($url, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
            'scope' => 'InvoicingAPI',
        ]);
        // 检查响应状态
        if ($response->successful()) {
            $accessToken = $response->json()['access_token'];
            $expiresIn = $response->json()['expires_in'];

            // 返回访问令牌或将其存储以供后续使用
            return response()->json([
                'access_token' => $accessToken,
                'expires_in' => $expiresIn,
            ]);
        } else {
            // 返回错误信息
            return response()->json([
                'error' => 'Login failed',
                'message' => $response->body(),
            ], $response->status());
        }
    }

    public function validateTIN($tin, $idType, $idValue)
    {
        // Step 1: 调用 login() 函数获取 access token
        $accessTokenResponse = $this->login();

        // 检查 access token 是否成功获取
        $accessToken = $accessTokenResponse->getData()->access_token ?? null;

        if (!$accessToken) {
            return response()->json(['error' => 'Failed to retrieve access token'], 500);
        }

        // Step 2: 构建 API 请求的 URL
        $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/taxpayer/validate/{$tin}?idType={$idType}&idValue={$idValue}";

        // Step 3: 设置请求头，包括 Authorization 和内容类型
        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => 'en',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        // Step 4: 发送 GET 请求到 API
        $response = Http::withHeaders($headers)->get($url);

        // Step 5: 检查响应状态并返回结果
        if ($response->successful()) {
            return response()->json([
                'message' => 'TIN validation successful',
                'data' => $response->json(),
            ]);
        } else {
            return response()->json([
                'error' => 'TIN validation failed',
                'message' => $response->body(),
            ], $response->status());
        }
    }

    public function submit(Request $request){
        $request->validate([
            'invoices' => 'required|array',
            'invoices.*.id' => 'required|integer',
        ]);
        $selectedInvoices = $request->input('invoices');
        $accessTokenResponse = $this->login();
        $accessToken = $accessTokenResponse->getData()->access_token ?? null;

        // if (!$accessToken) {
        //     return response()->json(['error' => 'Failed to retrieve access token'], 500);
        // }
        // $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/documentsubmissions";
        $url = route('mock.document-submission');
        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => 'en',
            'Content-Type' => 'application/json', 
            'Authorization' => 'Bearer ' . $accessToken, 
        ];
        $documents = [];
        foreach ($selectedInvoices as $invoice) {
            $invoice = Invoice::find($invoice['id']);
            $document = $this->generateXmlInvoice($invoice['id']);
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
            foreach ($response->json()['acceptedDocuments'] as $document) {
                $uuid = $document['uuid'];
                $invoiceCodeNumber = $document['invoiceCodeNumber'];
        
                // Call the getDocumentDetails function for each document
                $documentDetails = $this->testGetDocumentDetails($uuid);
                if (isset($documentDetails['uuid']) && isset($documentDetails['longId'])) {
                    $generatedPdf = $this->generateAndSavePdf($documentDetails, $invoiceCodeNumber);
                    
                    if ($generatedPdf) {
                        // PDF generated successfully, you can log or return success if needed
                        echo "PDF generated successfully for Invoice: $invoiceCodeNumber \n";
                    } else {
                        echo "Failed to generate PDF for Invoice: $invoiceCodeNumber \n";
                    }
                } else {
                    echo "Failed to retrieve document details for UUID: $uuid \n";
                }
            }

            return response()->json([
                'message' => 'Document submission successful',
                'submission_id' => $response->json()['submission_id'] ?? null,
                'document_ids' => $response->json()['document_ids'] ?? [],
            ]);
        } else {
            return response()->json([
                'error' => 'Document submission failed',
                'message' => $response->body(),
            ], $response->status());
        }
    }

    public function getDocumentDetails($uuid, $invoiceCodeNumber)
    {
        // Your existing code for getDocumentDetails
       

        $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/documents/{$uuid}/details";
        $accessTokenResponse = $this->login();
        $accessToken = $accessTokenResponse->getData()->access_token ?? null;

        if (!$accessToken) {
            return ['error' => 'Failed to retrieve access token'];
        }

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $response = Http::withHeaders($headers)->get($url);

        if ($response->successful()) {
            return [
                'uuid' => $response->json()['uuid'] ?? null,
                'longId' => $response->json()['longId'] ?? null,
            ];
        } else {
            return ['error' => 'Failed to retrieve document details', 'message' => $response->body()];
        }
    }

    public function generateAndSavePdf($documentDetails, $invoiceCodeNumber)
    {
        $uuid = $documentDetails['uuid'];
        $longId = $documentDetails['longId'];

        $invoice = Invoice::where('sku', $invoiceCodeNumber)->first();
        $delivery = DeliveryOrder::where('invoice_id', $invoice->id)->first();
        $firstDeliveryProduct = $delivery->products()->first();
        $deliveryProduct = $delivery->products()->get();

        $saleProduct = $firstDeliveryProduct->saleProduct;
        $sale = $saleProduct->sale;
        $saleProducts = $sale->products;

        $qrCode = $this->generateQrCode($uuid,$longId);
        $customer = $sale->customer;
        $envbaseurl = 'https://einvoice-portal.example.com';
        // 构建验证链接
        $validationLink = "{$envbaseurl}/{$uuid}/share/{$longId}";
        $pdf = Pdf::loadView('invoice.' . 'pdf.powercool' . '_inv_pdf', [
            'date' => now()->format('d/m/Y'),
            'sku' => $invoiceCodeNumber,
            'do_sku' => 'join(', ', $do_sku)',
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
        
        Storage::put('e-invoices/pdf/XML-INV12345.pdf', "ww". $content);
    }

    public function sendEmail($customerId,$invoiceId){
        $customer = Customer::findOrFail($customerId);
        $invoice = Invoice::findOrFail($invoiceId);
      
        // 使用Mailable发送邮件，附上PDF发票
        Mail::to($customer->email)->send(new EInvoiceEmail($customer, $invoice));

        return response()->json(['message' => '发票邮件已成功发送。']);
    }
    

    public function test(){
        $uuid = 'uuid';
        $longId = 'lonfId';

        $invoice = Invoice::where('sku', 'INV2411prOIF')->first();
        $delivery = DeliveryOrder::where('invoice_id', $invoice->id)->first();
        $firstDeliveryProduct = $delivery->products()->first();
        $deliveryProduct = $delivery->products()->get();
        $saleProduct = $firstDeliveryProduct->saleProduct;
        $sale = $saleProduct->sale;
        $saleProducts = $sale->products;
        $customer = $sale->customer;

        $envbaseurl = 'https://einvoice-portal.example.com';
        // 构建验证链接
        $validationLink = "{$envbaseurl}/{$uuid}/share/{$longId}";
    
        // $base64QrCode = base64_encode($qrCode);
        return view('invoice.pdf.powercool_inv_pdf',[
            'date' => now()->format('d/m/Y'),
            'sku' => 'INV2411prOIF',
            'do_sku' => 'join(', ', $do_sku)',
            'dos' => '$dos',
            'do_products' => $deliveryProduct,
            'customer' => $customer,
            'billing_address' => (new CustomerLocation)->defaultBillingAddress($customer->id),
            'terms' => '', 
            'validationLink' => $validationLink,
            'delivery_address' => CustomerLocation::find($sale->delivery_address_id)
        ]);
    }

    public function generateQrCode($uuid, $longId)
    {
        // 定义 e-Invoice 基础 URL
        $envbaseurl = 'https://einvoice-portal.example.com';
        // 构建验证链接
        $validationLink = "{$envbaseurl}/{$uuid}/share/{$longId}";
        // dd(QrCode::size(300)->generate($validationLink));

        $qrCode = QrCode::size(100)->generate($validationLink);

        // 返回 QR 代码作为响应
        return $qrCode;
    }

    public function generateXmlInvoice($id)
    {
        $invoice = Invoice::find($id);
        $delivery = DeliveryOrder::where('invoice_id',$id)->first();
        $deliveryProduct = $delivery->products()->first();
        $saleProduct = $deliveryProduct->saleProduct;
        $sale = $saleProduct->sale;
        $saleProducts = $sale->products;
        $sellerIDType = "";
        $sellerIDValue = ""; 
        $sellerTIN = "IG26663185010";
        $buyerTIN = "IG26663185010";
        $buyerIDType = "";
        $buyerIDValue = ""; 
        // $this->validateTIN($sellerTIN,$sellerIDType,$sellerIDValue);
        // $this->validateTIN($sellerTIN,$buyerIDType,$buyerIDValue);
        // 创建 XML DOMDocument 实例
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // 创建根元素 <Invoice>
        $invoiceElement = $xml->createElement('Invoice');
        $invoiceElement->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $invoiceElement->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoiceElement->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->appendChild($invoiceElement);

        // $ublExtensions = $this->createUBLExtensions($xml);
        // $invoiceElement->appendChild($ublExtensions);

        // 添加 <cbc:ID> 元素
        //this is invoice number (sku)
        $cbcId = $xml->createElement('cbc:ID', 'XML-INV12345');
        $invoiceElement->appendChild($cbcId);

        // 添加 <cbc:IssueDate> 元素
        $utcDateTime = new DateTime("now", new DateTimeZone("UTC"));
        $currentDate = $utcDateTime->format("Y-m-d");
        $cbcIssueDate = $xml->createElement('cbc:IssueDate', $currentDate);
        $invoiceElement->appendChild($cbcIssueDate);

        $currentTime = $utcDateTime->format("H:i:s") . "Z";
        $cbcIssueTime = $xml->createElement('cbc:IssueTime', $currentTime);
        $invoiceElement->appendChild($cbcIssueTime);

        // 添加 <cbc:InvoiceTypeCode> 元素
        $invoiceTypeCode = $xml->createElement('cbc:InvoiceTypeCode', '01');
        $invoiceTypeCode->setAttribute('listVersionID', '1.0');
        $invoiceElement->appendChild($invoiceTypeCode);

        // 添加 <cbc:DocumentCurrencyCode> 元素
        $currencyCode = $xml->createElement('cbc:DocumentCurrencyCode', 'MYR');
        $invoiceElement->appendChild($currencyCode);

        // 添加 <cbc:TaxCurrencyCode> 元素
        //optional
        // $taxCurrencyCode = $xml->createElement('cbc:TaxCurrencyCode', 'MYR');
        // $invoiceElement->appendChild($taxCurrencyCode);

        // 添加更多复杂结构如 <cac:InvoicePeriod>, <cac:BillingReference> 等
        //optional
        // $invoicePeriod = $this->createInvoicePeriod($xml, '2024-07-01', '2024-07-31', 'Monthly');
        // $invoiceElement->appendChild($invoicePeriod);   

        //<cac:BillingReference>
        $billingReference = $this->createBillingReference($xml, '151891-1981');
        $invoiceElement->appendChild($billingReference);

        $additionalDocumentReference1 = $this->createAdditionalDocumentReference($xml, 'L1', 'CustomsImportForm');
        $invoiceElement->appendChild($additionalDocumentReference1);

        // 附加第二个 AdditionalDocumentReference 节点，包含 DocumentDescription
        $additionalDocumentReference2 = $this->createAdditionalDocumentReference($xml, 'FTA', 'FreeTradeAgreement', 'Sample Description');
        $invoiceElement->appendChild($additionalDocumentReference2);

        // 附加第三个 AdditionalDocumentReference 节点，不包含 DocumentDescription
        $additionalDocumentReference3 = $this->createAdditionalDocumentReference($xml, 'L1', 'K2');
        $invoiceElement->appendChild($additionalDocumentReference3);

        // 附加第四个 AdditionalDocumentReference 节点，仅包含 ID
        $additionalDocumentReference4 = $this->createAdditionalDocumentReference($xml, 'L1');
        $invoiceElement->appendChild($additionalDocumentReference4);
        // 继续添加其他元素...

        $signatureElement = $this->createSignatureElement(
            $xml, 
            'urn:oasis:names:specification:ubl:signature:Invoice', 
            'urn:oasis:names:specification:ubl:dsig:enveloped:xades'
        );
        $invoiceElement->appendChild($signatureElement);

        // 创建 AccountingSupplierParty 节点并附加到 invoiceElement
        $accountingSupplierParty = $this->createAccountingSupplierPartyElement($xml,$sellerTIN);
        $invoiceElement->appendChild($accountingSupplierParty);

        $accountingCustomerParty = $this->createAccountingCustomerPartyElement($xml,$buyerTIN);
        $invoiceElement->appendChild($accountingCustomerParty);

        $deliveryElement = $this->createDeliveryElement($xml);
        $invoiceElement->appendChild($deliveryElement);

        $paymentMeansElement = $this->createPaymentMeansElement($xml);
        $invoiceElement->appendChild($paymentMeansElement);

        $paymentTermsElement = $this->createPaymentTermsElement($xml);
        $invoiceElement->appendChild($paymentTermsElement);

        $prepaidPaymentElement = $this->createPrepaidPaymentElement($xml);
        $invoiceElement->appendChild($prepaidPaymentElement);

        $allowanceCharge1 = $this->createAllowanceChargeElement($xml, false, 'Sample Description', 100);
        $invoiceElement->appendChild($allowanceCharge1);

        // 创建第二个 AllowanceCharge 节点并附加到 invoiceElement
        $allowanceCharge2 = $this->createAllowanceChargeElement($xml, true, 'Service charge', 100);
        $invoiceElement->appendChild($allowanceCharge2);

        $taxTotal = $this->createTaxTotalElement($xml, 87.63, 87.63);
        $invoiceElement->appendChild($taxTotal);

        $legalMonetaryTotal = $this->createLegalMonetaryTotalElement(
            $xml, 
            1436.50, 
            1436.50, 
            1436.50, 
            1436.50, 
            1436.50, 
            0.30, 
            1436.50
        );
        $invoiceElement->appendChild($legalMonetaryTotal);

        $allowanceCharges = [
            [
                'chargeIndicator' => false,
                'reason' => 'Sample Description',
                'multiplierFactor' => 0.15,
                'amount' => 100
            ],
            [
                'chargeIndicator' => true,
                'reason' => 'Sample Description',
                'multiplierFactor' => 0.10,
                'amount' => 100
            ]
        ];
        
        // 创建 InvoiceLine 元素并附加到 invoiceElement
        foreach ($saleProducts as $saleProduct) {
            // 提取每个产品的信息
            $id = $saleProduct->id; // 产品 ID
            $invoicedQuantity = $saleProduct->qty; // 数量
            $lineExtensionAmount = $saleProduct->qty * $saleProduct->unit_price; // 行金额
            $taxAmount = 0; // 预设税额为 0，可以根据需要计算
            $taxableAmount = $lineExtensionAmount; // 可征税金额
            $taxPercent = 6.00; // 税率，假设为 6.00
            $taxExemptionReason = 'Exempt New Means of Transport'; // 税收豁免原因
            $description = $saleProduct->desc ?? 'No Description'; // 产品描述
            $originCountryCode = 'MYS'; // 产地国家代码
            $itemClassificationCode = $saleProduct->product->sku; // 产品分类代码
            $priceAmount = $saleProduct->unit_price; // 单价
            $itemPriceExtensionAmount = $lineExtensionAmount; // 产品价格扩展金额
            $allowanceCharges = []; // 根据需要提供免除费用
        
            // 调用 createInvoiceLineElement 方法创建发票行元素
            $invoiceLine = $this->createInvoiceLineElement(
                $xml,
                (string) $id,
                $invoicedQuantity,
                $lineExtensionAmount,
                $allowanceCharges,
                $taxAmount,
                $taxableAmount,
                $taxPercent,
                $taxExemptionReason,
                $description,
                $originCountryCode,
                $itemClassificationCode,
                $priceAmount,
                $itemPriceExtensionAmount
            );
        
            // 将生成的发票行元素添加到发票元素中
            $invoiceElement->appendChild($invoiceLine);
        }
        // $xml,
        // string $id,
        // float $invoicedQuantity,
        // float $lineExtensionAmount,
        // array $allowanceCharges,
        // float $taxAmount,
        // float $taxableAmount,
        // float $taxPercent,
        // string $taxExemptionReason,
        // string $description,
        // string $originCountryCode,
        // string $itemClassificationCode,
        // float $priceAmount,
        // float $itemPriceExtensionAmount
        
        // 返回 XML 内容
        $xmlContent = $xml->saveXML();
        Storage::put('e-invoices/XML-INV12345.xml', $xmlContent);

        return $xmlContent;
    }

    private function createSignatureInformation($xml)
    {
        // 创建签名信息节点
        
        $signatureInformation = $xml->createElement('sac:SignatureInformation');

        // 添加 ID 和参考 ID
        $cbcID = $xml->createElement('cbc:ID', 'urn:oasis:names:specification:ubl:signature:1');
        $referencedSignatureID = $xml->createElement('sbc:ReferencedSignatureID', 'urn:oasis:names:specification:ubl:signature:Invoice');
        $signatureInformation->appendChild($cbcID);
        $signatureInformation->appendChild($referencedSignatureID);

        // 创建 ds:Signature 元素和 SignedInfo 部分
        $signature = $xml->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Signature');
        
        $signedInfo = $this->createSignedInfo($xml);
        $signature->appendChild($signedInfo);

        // 添加签名值
        $signatureValue = $xml->createElement('ds:SignatureValue', 'kZhLB843E/sJEd66jI1lcfRheCZXaaHs9EjYOktMy9f/Q');
        $signature->appendChild($signatureValue);

        // 添加密钥信息
        $keyInfo = $this->createKeyInfo($xml);
        $signature->appendChild($keyInfo);

        $object = $this->createObject($xml);
        $signature->appendChild($object);

        $signatureInformation->appendChild($signature);

        return $signatureInformation;
    }

    /**
     * 创建 SignedInfo 节点
     */
    private function createSignedInfo($xml)
    {
        $signedInfo = $xml->createElement('ds:SignedInfo');

        // 规范化方法
        $canonicalizationMethod = $xml->createElement('ds:CanonicalizationMethod');
        $canonicalizationMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
        $signedInfo->appendChild($canonicalizationMethod);

        // 签名方法
        $signatureMethod = $xml->createElement('ds:SignatureMethod');
        $signatureMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');
        $signedInfo->appendChild($signatureMethod);

        // 添加 Reference 节点
        $reference1 = $this->createReferenceId($xml, 'id-doc-signed-data', '');
        $signedInfo->appendChild($reference1);

        // 添加第二个 Reference 节点
        $reference2 = $this->createReferenceType($xml, 'id-xades-signed-props', 'http://www.w3.org/2000/09/xmldsig#SignatureProperties');
        $signedInfo->appendChild($reference2);

        return $signedInfo;
    }

    /**
     * 创建 Reference 节点
     */
    private function createReferenceId($xml, $id, $uri)
    {
        $reference = $xml->createElement('ds:Reference');
        $reference->setAttribute('Id', $id);
        $reference->setAttribute('URI', $uri);

        $transforms1 = $xml->createElement('ds:Transforms');
        $reference->appendChild($transforms1);

        $transform1_1 = $xml->createElement('ds:Transform');
        $transform1_1->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
        $xpath1_1 = $xml->createElement('ds:XPath', 'not(//ancestor-or-self::ext:UBLExtensions)');
        $transform1_1->appendChild($xpath1_1);
        $transforms1->appendChild($transform1_1);

        $transform1_2 = $xml->createElement('ds:Transform');
        $transform1_2->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
        $xpath1_2 = $xml->createElement('ds:XPath', 'not(//ancestor-or-self::cac:Signature)');
        $transform1_2->appendChild($xpath1_2);
        $transforms1->appendChild($transform1_2);

        $transform1_3 = $xml->createElement('ds:Transform');
        $transform1_3->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
        $transforms1->appendChild($transform1_3);

        // DigestMethod and DigestValue
        $digestMethod = $xml->createElement('ds:DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $digestValue = $xml->createElement('ds:DigestValue', 'your_digest_value_here');
        $reference->appendChild($digestMethod);
        $reference->appendChild($digestValue);

        return $reference;
    }

    private function createReferenceType($xml, $id, $uri)
    {
        $reference = $xml->createElement('ds:Reference');
        $reference->setAttribute('Type', $id);
        $reference->setAttribute('URI', $uri);

        // DigestMethod and DigestValue
        $digestMethod = $xml->createElement('ds:DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $digestValue = $xml->createElement('ds:DigestValue', 'your_digest_value_here');
        $reference->appendChild($digestMethod);
        $reference->appendChild($digestValue);

        return $reference;
    }

    /**
     * 创建 KeyInfo 节点
     */
    private function createKeyInfo($xml)
    {
        $keyInfo = $xml->createElement('ds:KeyInfo');
        $x509Data = $xml->createElement('ds:X509Data');
        $x509Certificate = $xml->createElement('ds:X509Certificate', 'MIIFlDCCA3ygAwIBAgIQeomZorO+0AwmW2BRdWJMxT');
        $x509Data->appendChild($x509Certificate);
        $keyInfo->appendChild($x509Data);

        return $keyInfo;
    }

    private function createObject($xml)
    {
        $object = $xml->createElement('ds:Object');

        // 创建 <xades:QualifyingProperties> 元素
        $qualifyingProperties = $xml->createElement('xades:QualifyingProperties');
        $qualifyingProperties->setAttribute('xmlns:xades', 'http://uri.etsi.org/01903/v1.3.2#');
        $qualifyingProperties->setAttribute('Target', 'signature');

        // 创建 <xades:SignedProperties> 元素
        $signedProperties = $xml->createElement('xades:SignedProperties');
        $signedProperties->setAttribute('Id', 'id-xades-signed-props');

        // 创建 <xades:SignedSignatureProperties> 元素
        $signedSignatureProperties = $xml->createElement('xades:SignedSignatureProperties');

        // 添加 <xades:SigningTime>
        $signingTime = $xml->createElement('xades:SigningTime', '2024-07-23T16:31:06Z');
        $signedSignatureProperties->appendChild($signingTime);

        // 创建 <xades:SigningCertificate>
        $signingCertificate = $xml->createElement('xades:SigningCertificate');
        $cert = $xml->createElement('xades:Cert');

        // 添加 <xades:CertDigest> 和子元素
        $certDigest = $xml->createElement('xades:CertDigest');
        $digestMethod = $xml->createElement('ds:DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
        $digestValue = $xml->createElement('ds:DigestValue', 'KKBSTyiPKGkGl1AFqcPziKCEIDYGtnYUTQN4ukO7G40=');

        $certDigest->appendChild($digestMethod);
        $certDigest->appendChild($digestValue);

        // 将 <xades:CertDigest> 添加到 <xades:Cert>
        $cert->appendChild($certDigest);

        // 创建 <xades:IssuerSerial> 和子元素
        $issuerSerial = $xml->createElement('xades:IssuerSerial');
        $x509IssuerName = $xml->createElement('ds:X509IssuerName', 'CN=Trial LHDNM Sub CA V1, OU=Terms of use at http://www.posdigicert.com.my, O=LHDNM, C=MY');
        $x509SerialNumber = $xml->createElement('ds:X509SerialNumber', '162880276254639189035871514749820882117');

        $issuerSerial->appendChild($x509IssuerName);
        $issuerSerial->appendChild($x509SerialNumber);

        // 将 <xades:IssuerSerial> 添加到 <xades:Cert>
        $cert->appendChild($issuerSerial);

        // 将 <xades:Cert> 添加到 <xades:SigningCertificate>
        $signingCertificate->appendChild($cert);

        // 将 <xades:SigningCertificate> 添加到 <xades:SignedSignatureProperties>
        $signedSignatureProperties->appendChild($signingCertificate);

        // 将 <xades:SignedSignatureProperties> 添加到 <xades:SignedProperties>
        $signedProperties->appendChild($signedSignatureProperties);

        // 将 <xades:SignedProperties> 添加到 <xades:QualifyingProperties>
        $qualifyingProperties->appendChild($signedProperties);

        // 将 <xades:QualifyingProperties> 添加到 <ds:Object>
        $object->appendChild($qualifyingProperties);

        return $object;
    }

    private function createUBLExtensions($xml)
    {
        $ublExtensions = $xml->createElementNS('urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2', 'UBLExtensions');
        
        $UBLExtension = $xml->createElement('UBLExtension');
        $ublExtensions->appendChild($UBLExtension);

        $ExtensionURI = $xml->createElement('ExtensionURI', 'urn:oasis:names:specification:ubl:dsig:enveloped:xades');
        $UBLExtension->appendChild($ExtensionURI);

        $ExtensionContent = $xml->createElement('ExtensionContent');
        $UBLExtension->appendChild($ExtensionContent);

        $ublDocumentSignatures = $xml->createElementNS(
            'urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2', 
            'sig:UBLDocumentSignatures'
        );
        $ublDocumentSignatures->setAttribute('xmlns:sig', 'urn:oasis:names:specification:ubl:schema:xsd:CommonSignatureComponents-2');
        $ublDocumentSignatures->setAttribute('xmlns:sac', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureAggregateComponents-2');
        $ublDocumentSignatures->setAttribute('xmlns:sbc', 'urn:oasis:names:specification:ubl:schema:xsd:SignatureBasicComponents-2');
        $ExtensionContent->appendChild($ublDocumentSignatures);

        $signatureInformation = $this->createSignatureInformation($xml);
        $ublDocumentSignatures->appendChild($signatureInformation);

        return $ublExtensions;
    }
    
    public function createInvoicePeriod($xml,$startDate,$endDate,$description)
    {
        //three of these optional
        // 创建 InvoicePeriod 元素
        $invoicePeriod = $xml->createElement('cac:InvoicePeriod');
        
        // 创建并附加 StartDate 元素
        $startDateElement = $xml->createElement('cbc:StartDate', $startDate);
        $invoicePeriod->appendChild($startDateElement);
        
        // 创建并附加 EndDate 元素
        $endDateElement = $xml->createElement('cbc:EndDate', $endDate);
        $invoicePeriod->appendChild($endDateElement);
        
        // 创建并附加 Description 元素
        $descriptionElement = $xml->createElement('cbc:Description', $description);
        $invoicePeriod->appendChild($descriptionElement);
        
        return $invoicePeriod;
    }

    public function createBillingReference($xml,$documentId)
    {
        // 创建 BillingReference 元素
        $billingReference = $xml->createElement('cac:BillingReference');
        
        // 创建 AdditionalDocumentReference 元素
        $additionalDocumentReference = $xml->createElement('cac:AdditionalDocumentReference');
        
        // 创建并附加 ID 元素
        $idElement = $xml->createElement('cbc:ID', $documentId);
        $additionalDocumentReference->appendChild($idElement);
        
        // 将 AdditionalDocumentReference 添加到 BillingReference
        $billingReference->appendChild($additionalDocumentReference);
        
        return $billingReference;
    }

    public function createAdditionalDocumentReference($xml, $documentId,$documentType = null,$documentDescription = null)
    {
        // 创建 AdditionalDocumentReference 元素
        $additionalDocumentReference = $xml->createElement('cac:AdditionalDocumentReference');
        
        // 创建并附加 ID 元素
        $idElement = $xml->createElement('cbc:ID', $documentId);
        $additionalDocumentReference->appendChild($idElement);
        
        // 根据需要创建并附加 DocumentType 元素
        if ($documentType !== null) {
            $documentTypeElement = $xml->createElement('cbc:DocumentType', $documentType);
            $additionalDocumentReference->appendChild($documentTypeElement);
        }
        
        // 根据需要创建并附加 DocumentDescription 元素
        if ($documentDescription !== null) {
            $documentDescriptionElement = $xml->createElement('cbc:DocumentDescription', $documentDescription);
            $additionalDocumentReference->appendChild($documentDescriptionElement);
        }
        
        return $additionalDocumentReference;
    }

    public function createSignatureElement($xml, $signatureId,$signatureMethod)
    {
        // 创建 Signature 元素
        $signatureElement = $xml->createElement('cac:Signature');
        
        // 创建并附加 ID 元素
        $idElement = $xml->createElement('cbc:ID', $signatureId);
        $signatureElement->appendChild($idElement);
        
        // 创建并附加 SignatureMethod 元素
        $signatureMethodElement = $xml->createElement('cbc:SignatureMethod', $signatureMethod);
        $signatureElement->appendChild($signatureMethodElement);
        
        return $signatureElement;
    }

    public function createAccountingSupplierPartyElement($xml,$sellerTIN)
    {
        // 创建 AccountingSupplierParty 元素
        $accountingSupplierParty = $xml->createElement('cac:AccountingSupplierParty');

        // 添加 AdditionalAccountID
        $additionalAccountID = $xml->createElement('cbc:AdditionalAccountID', 'CPT-CCN-W-211111-KL-000002');
        $additionalAccountID->setAttribute('schemeAgencyName', 'PowerCool');
        $accountingSupplierParty->appendChild($additionalAccountID);

        // 创建 Party 节点
        $party = $xml->createElement('cac:Party');
        $accountingSupplierParty->appendChild($party);

        // 添加 IndustryClassificationCode
        $industryClassificationCode = $xml->createElement('cbc:IndustryClassificationCode', $this->msic);
        $industryClassificationCode->setAttribute('name', 'Wholesale of Refrigrerator');
        $party->appendChild($industryClassificationCode);

        // 添加 PartyIdentification 节点
        $partyIdentifications = [
            ['schemeID' => 'TIN', 'ID' => $sellerTIN],
            ['schemeID' => 'BRN', 'ID' => "202001234567"],
            ['schemeID' => 'SST', 'ID' => 'A01-2345-67891012'],
        ];

        foreach ($partyIdentifications as $identification) {
            $partyIdentification = $xml->createElement('cac:PartyIdentification');
            $idElement = $xml->createElement('cbc:ID', $identification['ID']);
            $idElement->setAttribute('schemeID', $identification['schemeID']);
            $partyIdentification->appendChild($idElement);
            $party->appendChild($partyIdentification);
        }

        // 添加 PostalAddress
        $postalAddress = $xml->createElement('cac:PostalAddress');
        $cityName = $xml->createElement('cbc:CityName', 'Kuala Lumpur');
        $postalZone = $xml->createElement('cbc:PostalZone', '50480');
        $countrySubentityCode = $xml->createElement('cbc:CountrySubentityCode', '14');
        $postalAddress->appendChild($cityName);
        $postalAddress->appendChild($postalZone);
        $postalAddress->appendChild($countrySubentityCode);

        // 添加 AddressLine
        $addressLines = ['Lot 66', 'Bangunan Merdeka', 'Persiaran Jaya'];
        foreach ($addressLines as $line) {
            $addressLine = $xml->createElement('cac:AddressLine');
            $lineElement = $xml->createElement('cbc:Line', $line);
            $addressLine->appendChild($lineElement);
            $postalAddress->appendChild($addressLine);
        }

        // 添加 Country
        $country = $xml->createElement('cac:Country');
        $identificationCode = $xml->createElement('cbc:IdentificationCode', 'MYS');
        $identificationCode->setAttribute('listID', 'ISO3166-1');
        $identificationCode->setAttribute('listAgencyID', '6');
        $country->appendChild($identificationCode);
        $postalAddress->appendChild($country);

        $party->appendChild($postalAddress);

        // 添加 PartyLegalEntity
        $partyLegalEntity = $xml->createElement('cac:PartyLegalEntity');
        $registrationName = $xml->createElement('cbc:RegistrationName', "PowerCool");
        $partyLegalEntity->appendChild($registrationName);
        $party->appendChild($partyLegalEntity);

        // 添加 Contact
        $contact = $xml->createElement('cac:Contact');
        $telephone = $xml->createElement('cbc:Telephone', '+60123456789');
        $email = $xml->createElement('cbc:ElectronicMail', 'supplier@email.com');
        $contact->appendChild($telephone);
        $contact->appendChild($email);
        $party->appendChild($contact);

        return $accountingSupplierParty;
    }

    public function createAccountingCustomerPartyElement($xml,$buyerTIN)
    {
        // 创建 AccountingCustomerParty 元素
        $accountingCustomerParty = $xml->createElement('cac:AccountingCustomerParty');

        // 创建 Party 节点
        $party = $xml->createElement('cac:Party');
        $accountingCustomerParty->appendChild($party);

        // 添加 PartyIdentification 节点
        $partyIdentifications = [
            ['schemeID' => 'TIN', 'ID' => $buyerTIN],
            ['schemeID' => 'BRN', 'ID' => "202001234567"],
            ['schemeID' => 'SST', 'ID' => 'NA'],
        ];

        foreach ($partyIdentifications as $identification) {
            $partyIdentification = $xml->createElement('cac:PartyIdentification');
            $idElement = $xml->createElement('cbc:ID', $identification['ID']);
            $idElement->setAttribute('schemeID', $identification['schemeID']);
            $partyIdentification->appendChild($idElement);
            $party->appendChild($partyIdentification);
        }

        // 添加 PostalAddress
        $postalAddress = $xml->createElement('cac:PostalAddress');
        $cityName = $xml->createElement('cbc:CityName', 'Kuala Lumpur');
        $postalZone = $xml->createElement('cbc:PostalZone', '50480');
        $countrySubentityCode = $xml->createElement('cbc:CountrySubentityCode', '14');
        $postalAddress->appendChild($cityName);
        $postalAddress->appendChild($postalZone);
        $postalAddress->appendChild($countrySubentityCode);

        // 添加 AddressLine
        $addressLines = ['Lot 66', 'Bangunan Merdeka', 'Persiaran Jaya'];
        foreach ($addressLines as $line) {
            $addressLine = $xml->createElement('cac:AddressLine');
            $lineElement = $xml->createElement('cbc:Line', $line);
            $addressLine->appendChild($lineElement);
            $postalAddress->appendChild($addressLine);
        }

        // 添加 Country
        $country = $xml->createElement('cac:Country');
        $identificationCode = $xml->createElement('cbc:IdentificationCode', 'MYS');
        $identificationCode->setAttribute('listID', 'ISO3166-1');
        $identificationCode->setAttribute('listAgencyID', '6');
        $country->appendChild($identificationCode);
        $postalAddress->appendChild($country);

        $party->appendChild($postalAddress);

        // 添加 PartyLegalEntity
        $partyLegalEntity = $xml->createElement('cac:PartyLegalEntity');
        $registrationName = $xml->createElement('cbc:RegistrationName', "Buyer's Name");
        $partyLegalEntity->appendChild($registrationName);
        $party->appendChild($partyLegalEntity);

        // 添加 Contact
        $contact = $xml->createElement('cac:Contact');
        $telephone = $xml->createElement('cbc:Telephone', '+60123456780');
        $email = $xml->createElement('cbc:ElectronicMail', 'buyer@email.com');
        $contact->appendChild($telephone);
        $contact->appendChild($email);
        $party->appendChild($contact);

        return $accountingCustomerParty;
    }

    public function createDeliveryElement($xml)
    {
        // 创建 Delivery 元素
        $delivery = $xml->createElement('cac:Delivery');

        // 创建 DeliveryParty 节点
        $deliveryParty = $xml->createElement('cac:DeliveryParty');

        // 添加 PartyIdentification 节点
        $partyIdentifications = [
            ['schemeID' => 'TIN', 'ID' => "IG26663185010"],
            ['schemeID' => 'BRN', 'ID' => "Recipient's BRN"],
        ];

        foreach ($partyIdentifications as $identification) {
            $partyIdentification = $xml->createElement('cac:PartyIdentification');
            $idElement = $xml->createElement('cbc:ID', $identification['ID']);
            $idElement->setAttribute('schemeID', $identification['schemeID']);
            $partyIdentification->appendChild($idElement);
            $deliveryParty->appendChild($partyIdentification);
        }

        // 添加 PostalAddress
        $postalAddress = $xml->createElement('cac:PostalAddress');
        $postalAddress->appendChild($xml->createElement('cbc:CityName', 'Kuala Lumpur'));
        $postalAddress->appendChild($xml->createElement('cbc:PostalZone', '50480'));
        $postalAddress->appendChild($xml->createElement('cbc:CountrySubentityCode', '14'));

        $addressLines = ['Lot 66', 'Bangunan Merdeka', 'Persiaran Jaya'];
        foreach ($addressLines as $line) {
            $addressLine = $xml->createElement('cac:AddressLine');
            $lineElement = $xml->createElement('cbc:Line', $line);
            $addressLine->appendChild($lineElement);
            $postalAddress->appendChild($addressLine);
        }

        $country = $xml->createElement('cac:Country');
        $identificationCode = $xml->createElement('cbc:IdentificationCode', 'MYS');
        $identificationCode->setAttribute('listID', 'ISO3166-1');
        $identificationCode->setAttribute('listAgencyID', '6');
        $country->appendChild($identificationCode);
        $postalAddress->appendChild($country);

        $deliveryParty->appendChild($postalAddress);

        // 添加 PartyLegalEntity
        $partyLegalEntity = $xml->createElement('cac:PartyLegalEntity');
        $partyLegalEntity->appendChild($xml->createElement('cbc:RegistrationName', "Recipient's Name"));
        $deliveryParty->appendChild($partyLegalEntity);

        // 添加 DeliveryParty 到 Delivery
        $delivery->appendChild($deliveryParty);

        // 创建 Shipment 节点
        $shipment = $xml->createElement('cac:Shipment');
        $shipment->appendChild($xml->createElement('cbc:ID', '1234'));

        // 创建 FreightAllowanceCharge 节点
        $freightAllowanceCharge = $xml->createElement('cac:FreightAllowanceCharge');
        $freightAllowanceCharge->appendChild($xml->createElement('cbc:ChargeIndicator', 'true'));
        $freightAllowanceCharge->appendChild($xml->createElement('cbc:AllowanceChargeReason', 'Service charge'));

        $amountElement = $xml->createElement('cbc:Amount', '100');
        $amountElement->setAttribute('currencyID', 'MYR');
        $freightAllowanceCharge->appendChild($amountElement);

        $shipment->appendChild($freightAllowanceCharge);
        $delivery->appendChild($shipment);

        return $delivery;
    }

    public function createPaymentMeansElement($xml)
    {
        // 创建 PaymentMeans 元素
        $paymentMeans = $xml->createElement('cac:PaymentMeans');

        // 添加 PaymentMeansCode 节点
        $paymentMeansCode = $xml->createElement('cbc:PaymentMeansCode', '01');
        $paymentMeans->appendChild($paymentMeansCode);

        // 创建并添加 PayeeFinancialAccount 节点
        $payeeFinancialAccount = $xml->createElement('cac:PayeeFinancialAccount');
        $accountID = $xml->createElement('cbc:ID', '1234567890');
        $payeeFinancialAccount->appendChild($accountID);
        
        // 将 PayeeFinancialAccount 添加到 PaymentMeans
        $paymentMeans->appendChild($payeeFinancialAccount);

        return $paymentMeans;
    }

    public function createPaymentTermsElement($xml)
    {
        // 创建 PaymentTerms 元素
        $paymentTerms = $xml->createElement('cac:PaymentTerms');

        // 添加 Note 节点
        $note = $xml->createElement('cbc:Note', 'Payment method is cash');
        $paymentTerms->appendChild($note);

        return $paymentTerms;
    }

    public function createPrepaidPaymentElement($xml)
    {
        // 创建 PrepaidPayment 元素
        $prepaidPayment = $xml->createElement('cac:PrepaidPayment');

        // 添加 ID 节点
        $id = $xml->createElement('cbc:ID', 'E12345678912');
        $prepaidPayment->appendChild($id);

        // 添加 PaidAmount 节点
        $paidAmount = $xml->createElement('cbc:PaidAmount', '1.00');
        $paidAmount->setAttribute('currencyID', 'MYR');
        $prepaidPayment->appendChild($paidAmount);

        // 添加 PaidDate 节点
        $paidDate = $xml->createElement('cbc:PaidDate', '2024-07-23');
        $prepaidPayment->appendChild($paidDate);

        // 添加 PaidTime 节点
        $paidTime = $xml->createElement('cbc:PaidTime', '00:30:00Z');
        $prepaidPayment->appendChild($paidTime);

        return $prepaidPayment;
    }

    public function createAllowanceChargeElement($xml,$chargeIndicator,$reason,$amount,$currency = 'MYR')
    {
        // 创建 AllowanceCharge 元素
        $allowanceCharge = $xml->createElement('cac:AllowanceCharge');

        // 添加 ChargeIndicator 节点
        $chargeIndicatorElement = $xml->createElement('cbc:ChargeIndicator', $chargeIndicator ? 'true' : 'false');
        $allowanceCharge->appendChild($chargeIndicatorElement);

        // 添加 AllowanceChargeReason 节点
        $allowanceChargeReason = $xml->createElement('cbc:AllowanceChargeReason', $reason);
        $allowanceCharge->appendChild($allowanceChargeReason);

        // 添加 Amount 节点
        $amountElement = $xml->createElement('cbc:Amount', (string)$amount);
        $amountElement->setAttribute('currencyID', $currency);
        $allowanceCharge->appendChild($amountElement);

        return $allowanceCharge;
    }

    public function createTaxTotalElement($xml,$taxAmount,$taxableAmount,$taxSchemeID = 'OTH',$schemeID = 'UN/ECE 5153',$schemeAgencyID = '6')
    {
        // 创建 TaxTotal 元素
        $taxTotal = $xml->createElement('cac:TaxTotal');

        // 创建并添加 TaxAmount 节点
        $taxAmountElement = $xml->createElement('cbc:TaxAmount', (string)$taxAmount);
        $taxAmountElement->setAttribute('currencyID', 'MYR');
        $taxTotal->appendChild($taxAmountElement);

        // 创建 TaxSubtotal 元素
        $taxSubtotal = $xml->createElement('cac:TaxSubtotal');

        // 创建并添加 TaxableAmount 节点
        $taxableAmountElement = $xml->createElement('cbc:TaxableAmount', (string)$taxableAmount);
        $taxableAmountElement->setAttribute('currencyID', 'MYR');
        $taxSubtotal->appendChild($taxableAmountElement);

        // 创建并添加 TaxAmount 节点（同样的税额）
        $taxAmountSubtotalElement = $xml->createElement('cbc:TaxAmount', (string)$taxAmount);
        $taxAmountSubtotalElement->setAttribute('currencyID', 'MYR');
        $taxSubtotal->appendChild($taxAmountSubtotalElement);

        // 创建 TaxCategory 元素
        $taxCategory = $xml->createElement('cac:TaxCategory');

        // 添加 cbc:ID 节点到 TaxCategory
        $taxCategoryID = $xml->createElement('cbc:ID', '01');
        $taxCategory->appendChild($taxCategoryID);

        // 创建并添加 TaxScheme 元素
        $taxScheme = $xml->createElement('cac:TaxScheme');
        $taxSchemeIDElement = $xml->createElement('cbc:ID', $taxSchemeID);
        $taxSchemeIDElement->setAttribute('schemeID', $schemeID);
        $taxSchemeIDElement->setAttribute('schemeAgencyID', $schemeAgencyID);
        $taxScheme->appendChild($taxSchemeIDElement);

        // 将 TaxScheme 添加到 TaxCategory
        $taxCategory->appendChild($taxScheme);

        // 将 TaxCategory 添加到 TaxSubtotal
        $taxSubtotal->appendChild($taxCategory);

        // 将 TaxSubtotal 添加到 TaxTotal
        $taxTotal->appendChild($taxSubtotal);

        return $taxTotal;
    }

    public function createLegalMonetaryTotalElement(
        $xml, 
        float $lineExtensionAmount, 
        float $taxExclusiveAmount, 
        float $taxInclusiveAmount, 
        float $allowanceTotalAmount, 
        float $chargeTotalAmount, 
        float $payableRoundingAmount, 
        float $payableAmount
    ){
        // 创建 LegalMonetaryTotal 元素
        $legalMonetaryTotal = $xml->createElement('cac:LegalMonetaryTotal');
    
        // 创建并添加 LineExtensionAmount 节点
        // customer真正给的价钱
        $lineExtensionAmountElement = $xml->createElement('cbc:LineExtensionAmount', (string)$lineExtensionAmount);
        $lineExtensionAmountElement->setAttribute('currencyID', 'MYR');
        $legalMonetaryTotal->appendChild($lineExtensionAmountElement);
    
        // 创建并添加 TaxExclusiveAmount 节点
        //好像跟上面一样
        $taxExclusiveAmountElement = $xml->createElement('cbc:TaxExclusiveAmount', (string)$taxExclusiveAmount);
        $taxExclusiveAmountElement->setAttribute('currencyID', 'MYR');
        $legalMonetaryTotal->appendChild($taxExclusiveAmountElement);
    
        // 创建并添加 TaxInclusiveAmount 节点
        //全部包括tax
        $taxInclusiveAmountElement = $xml->createElement('cbc:TaxInclusiveAmount', (string)$taxInclusiveAmount);
        $taxInclusiveAmountElement->setAttribute('currencyID', 'MYR');
        $legalMonetaryTotal->appendChild($taxInclusiveAmountElement);
    
        // 创建并添加 AllowanceTotalAmount 节点
        //total discount多少
        $allowanceTotalAmountElement = $xml->createElement('cbc:AllowanceTotalAmount', (string)$allowanceTotalAmount);
        $allowanceTotalAmountElement->setAttribute('currencyID', 'MYR');
        $legalMonetaryTotal->appendChild($allowanceTotalAmountElement);
    
        // 创建并添加 ChargeTotalAmount 节点
        //税前charge的费用
        $chargeTotalAmountElement = $xml->createElement('cbc:ChargeTotalAmount', (string)$chargeTotalAmount);
        $chargeTotalAmountElement->setAttribute('currencyID', 'MYR');
        $legalMonetaryTotal->appendChild($chargeTotalAmountElement);
    
        // 创建并添加 PayableRoundingAmount 节点
        $payableRoundingAmountElement = $xml->createElement('cbc:PayableRoundingAmount', (string)$payableRoundingAmount);
        $payableRoundingAmountElement->setAttribute('currencyID', 'MYR');
        $legalMonetaryTotal->appendChild($payableRoundingAmountElement);
    
        // 创建并添加 PayableAmount 节点
        //总共费用，包括tax和discount，不包括提前给的费用
        $payableAmountElement = $xml->createElement('cbc:PayableAmount', (string)$payableAmount);
        $payableAmountElement->setAttribute('currencyID', 'MYR');
        $legalMonetaryTotal->appendChild($payableAmountElement);
    
        return $legalMonetaryTotal;
    }

    public function createInvoiceLineElement(
        $xml,
        string $id,
        float $invoicedQuantity,
        float $lineExtensionAmount,
        array $allowanceCharges,
        float $taxAmount,
        float $taxableAmount,
        float $taxPercent,
        string $taxExemptionReason,
        string $description,
        string $originCountryCode,
        string $itemClassificationCode,
        float $priceAmount,
        float $itemPriceExtensionAmount
    ) {
        $invoiceLine = $xml->createElement('cac:InvoiceLine');
    
        $idElement = $xml->createElement('cbc:ID', $id);
        $invoiceLine->appendChild($idElement);
    
        $invoicedQuantityElement = $xml->createElement('cbc:InvoicedQuantity', (string)$invoicedQuantity);
        $invoicedQuantityElement->setAttribute('unitCode', 'C62');
        $invoiceLine->appendChild($invoicedQuantityElement);
    
        $lineExtensionAmountElement = $xml->createElement('cbc:LineExtensionAmount', number_format($lineExtensionAmount, 2, '.', ''));
        $lineExtensionAmountElement->setAttribute('currencyID', 'MYR');
        $invoiceLine->appendChild($lineExtensionAmountElement);
    
        foreach ($allowanceCharges as $charge) {
            $allowanceCharge = $xml->createElement('cac:AllowanceCharge');
    
            $chargeIndicator = $xml->createElement('cbc:ChargeIndicator', $charge['chargeIndicator'] ? 'true' : 'false');
            $allowanceCharge->appendChild($chargeIndicator);
    
            $allowanceChargeReason = $xml->createElement('cbc:AllowanceChargeReason', $charge['reason']);
            $allowanceCharge->appendChild($allowanceChargeReason);
    
            $multiplierFactorNumeric = $xml->createElement('cbc:MultiplierFactorNumeric', number_format($charge['multiplierFactor'], 2, '.', ''));
            $allowanceCharge->appendChild($multiplierFactorNumeric);
    
            $amount = $xml->createElement('cbc:Amount', number_format($charge['amount'], 2, '.', ''));
            $amount->setAttribute('currencyID', 'MYR');
            $allowanceCharge->appendChild($amount);
    
            $invoiceLine->appendChild($allowanceCharge);
        }
    
        $taxTotal = $xml->createElement('cac:TaxTotal');
        $taxAmountElement = $xml->createElement('cbc:TaxAmount', number_format($taxAmount, 2, '.', ''));
        $taxAmountElement->setAttribute('currencyID', 'MYR');
        $taxTotal->appendChild($taxAmountElement);
    
        $taxSubtotal = $xml->createElement('cac:TaxSubtotal');
        $taxableAmountElement = $xml->createElement('cbc:TaxableAmount', number_format($taxableAmount, 2, '.', ''));
        $taxableAmountElement->setAttribute('currencyID', 'MYR');
        $taxSubtotal->appendChild($taxableAmountElement);
    
        $taxAmountElement2 = $xml->createElement('cbc:TaxAmount', number_format($taxAmount, 2, '.', ''));
        $taxAmountElement2->setAttribute('currencyID', 'MYR');
        $taxSubtotal->appendChild($taxAmountElement2);
    
        $percentElement = $xml->createElement('cbc:Percent', number_format($taxPercent, 2, '.', ''));
        $taxSubtotal->appendChild($percentElement);
    
        $taxCategory = $xml->createElement('cac:TaxCategory');
        $taxCategoryId = $xml->createElement('cbc:ID', 'E');
        $taxCategory->appendChild($taxCategoryId);
    
        $taxExemptionReasonElement = $xml->createElement('cbc:TaxExemptionReason', $taxExemptionReason);
        $taxCategory->appendChild($taxExemptionReasonElement);
    
        $taxScheme = $xml->createElement('cac:TaxScheme');
        $taxSchemeId = $xml->createElement('cbc:ID', 'OTH');
        $taxSchemeId->setAttribute('schemeID', 'UN/ECE 5153');
        $taxSchemeId->setAttribute('schemeAgencyID', '6');
        $taxScheme->appendChild($taxSchemeId);
        $taxCategory->appendChild($taxScheme);
    
        $taxSubtotal->appendChild($taxCategory);
        $taxTotal->appendChild($taxSubtotal);
        $invoiceLine->appendChild($taxTotal);
    
        $item = $xml->createElement('cac:Item');
        $descriptionElement = $xml->createElement('cbc:Description', $description);
        $item->appendChild($descriptionElement);
    
        $originCountry = $xml->createElement('cac:OriginCountry');
        $originCountryCodeElement = $xml->createElement('cbc:IdentificationCode', $originCountryCode);
        $originCountry->appendChild($originCountryCodeElement);
        $item->appendChild($originCountry);
    
        $commodityClassification1 = $xml->createElement('cac:CommodityClassification');
        $itemClassificationCode1 = $xml->createElement('cbc:ItemClassificationCode', $itemClassificationCode);
        $itemClassificationCode1->setAttribute('listID', 'PTC');
        $commodityClassification1->appendChild($itemClassificationCode1);
        $item->appendChild($commodityClassification1);
    
        // Add the second CommodityClassification element
        $commodityClassification2 = $xml->createElement('cac:CommodityClassification');
        $itemClassificationCode2 = $xml->createElement('cbc:ItemClassificationCode', '003');
        $itemClassificationCode2->setAttribute('listID', 'CLASS');
        $commodityClassification2->appendChild($itemClassificationCode2);
        $item->appendChild($commodityClassification2);
    
        $invoiceLine->appendChild($item);
    
        $price = $xml->createElement('cac:Price');
        $priceAmountElement = $xml->createElement('cbc:PriceAmount', number_format($priceAmount, 2, '.', ''));
        $priceAmountElement->setAttribute('currencyID', 'MYR');
        $price->appendChild($priceAmountElement);
        $invoiceLine->appendChild($price);
    
        $itemPriceExtension = $xml->createElement('cac:ItemPriceExtension');
        $amountElement = $xml->createElement('cbc:Amount', number_format($itemPriceExtensionAmount, 2, '.', ''));
        $amountElement->setAttribute('currencyID', 'MYR');
        $itemPriceExtension->appendChild($amountElement);
        $invoiceLine->appendChild($itemPriceExtension);
    
        return $invoiceLine;
    }

    
}
