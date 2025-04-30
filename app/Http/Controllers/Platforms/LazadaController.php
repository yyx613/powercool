<?php

namespace App\Http\Controllers\Platforms;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\Platform;
use App\Models\PlatformTokens;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LazadaController extends Controller
{
    protected $appKey; 
    protected $appSecret; 
    protected $endpoint;
    protected $accessToken;
    protected $platform;

    public function __construct()
    {
        $this->appKey = config('platforms.lazada.app_key');
        $this->appSecret = config('platforms.lazada.secret_key');
        $this->endpoint = 'https://api.lazada.com.my/rest';
        $this->platform = Platform::where('name','Lazada')->first();
        $this->accessToken = $this->platform ? PlatformTokens::where('platform_id',$this->platform->id)->first()->access_token : null;
    }

    public function handleLazadaWebhook(Request $request)
    {
        $messageBody = $request->getContent(); 
        Log::info('Lazada Webhook received', ['messageBody' => $messageBody]);

        $appKey = $this->appKey; 
        $appSecret = $this->appSecret; 

        $receivedSignature = $request->header('Authorization');

        $base = $appKey . $messageBody;
        $expectedSignature = $this->generateWebhookSignature($base, $appSecret);
        if (!hash_equals($expectedSignature, $receivedSignature)) {
            Log::warning('Signature mismatch', [
                'expectedSignature' => $expectedSignature,
                'receivedSignature' => $receivedSignature
            ]);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $data = json_decode($messageBody, true); 
        $orderId = $data['data']['trade_order_id'];
        $status = ($data['data']['order_status'] == 'returned' || $data['data']['order_status'] == 'unpaid' || $data['data']['order_status'] == 'cancelled') 
                    ? Sale::STATUS_INACTIVE 
                    : Sale::STATUS_ACTIVE;

        Log::info('Order ID and Status', ['orderId' => $orderId, 'status' => $status]);

        try {
            $sale = Sale::where('order_id', $orderId)->where('platform_id',$this->platform->id)->first();
            if ($sale) {
                $sale->update(['status' => $status]);
                Log::info('Order status updated', [
                    'orderId' => $orderId,
                    'newStatus' => $status
                ]);
            } else {
                Log::info('Order not found, fetching Lazada order items', ['orderId' => $orderId]);
                return $this->getLazadaOrderItems($orderId, $status);
            }
        } catch (\Throwable $th) {
            Log::error('Error handling Lazada Webhook', [
                'error' => $th->getMessage(),
                'orderId' => $orderId,
                'status' => $status
            ]); 
            dd($th);
        }

    }

    private function generateWebhookSignature($base, $secret) {
        $hmac = hash_hmac('sha256', $base, $secret, true);
    
        return strtolower(bin2hex($hmac));
    }
    

    public function getAccessTokenLazada($code){
        $endpoint = 'https://auth.lazada.com/rest/auth/token/create';

        $appKey = $this->appKey;
        $appSecret = $this->appSecret;

        $timestamp = round(microtime(true) * 1000);

        $params = [
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'sign_method' => 'sha256',
            'code' => $code, 
        ];

        try {
            $params['sign'] = $this->generateSignature($params, $appSecret, '/auth/token/create');

            $response = Http::get($endpoint, $params);
            if ($response->successful()) {
                $data = $response->json();

                $newAccessToken = $data['access_token'];
                $newRefreshToken = $data['refresh_token'];
                $expiresIn = $data['expires_in'];
                $refreshExpiresIn = $data['refresh_expires_in'];
                DB::beginTransaction();
                PlatformTokens::updateOrCreate(
                    ['platform_id' => $this->platform->id],
                    [
                        'access_token' => $newAccessToken,
                        'refresh_token' => $newRefreshToken,
                        'access_token_expires_at' => Carbon::now()->addSeconds($expiresIn),
                        'refresh_token_expires_at' => Carbon::now()->addSeconds($refreshExpiresIn),
                    ]
                );
                DB::commit();
                return 'success';
            }
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error(['Failed to token from Lazada API', 'message' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to token from Lazada API', 'message' => $e->getMessage()], 500);
        }
        
    }

    public function refreshAccessTokenLazada()
    {
        $appKey = $this->appKey;
        $appSecret = $this->appSecret;
        $refreshToken = PlatformTokens::where('platform_id',$this->platform->id)->first()->refresh_token;
        $url = $this->endpoint.'/auth/token/refresh';

        $timestamp = now()->timestamp * 1000;

        $params = [
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'refresh_token' => $refreshToken,
            'sign_method' => 'sha256',
        ];

        $sign = $this->generateSignature($params, $appSecret,'/auth/token/refresh');

        $params['sign'] = $sign;
        try {
            $response = Http::get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Received response from Lazada', ['data' => $data]);
                $newAccessToken = $data['access_token'];
                $newRefreshToken = $data['refresh_token'];
                $expiresIn = $data['expires_in'];
                $refreshExpiresIn = $data['refresh_expires_in'];

                DB::beginTransaction();
                PlatformTokens::where('platform_id', $this->platform->id)->update([
                    'access_token' => $newAccessToken,
                    'refresh_token' => $newRefreshToken,
                    'access_token_expires_at' => Carbon::now()->addSeconds($expiresIn),
                    'refresh_token_expires_at' => Carbon::now()->addSeconds($refreshExpiresIn),
                ]);
                DB::commit();

                Log::info('Updated platform token in database', [
                    'platform' => $this->platform->name,
                    'access_token' => $newAccessToken
                ]);

                return 'success';
            }else {
                Log::warning('Failed to receive successful response from Lazada', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json(['error' => 'Failed to fetch data from Lazada API'], 500);
            }
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to fetch data from Lazada API', 'message' => $e->getMessage()], 500);
        }
       
    }

    private function getLazadaOrder($orderId,$sale){
        $url = $this->endpoint.'/order/get';
        $appKey = $this->appKey;
        $appSecret = $this->appSecret; 

         $params = [
            'app_key' => $appKey,
            'timestamp' => time() * 1000, 
            'access_token' => $this->accessToken ,
            'sign_method' => 'sha256',
            'order_id' => $orderId, 
        ];

        $params['sign'] = $this->generateSignature($params, $appSecret, '/order/get');
        Log::info('Fetching Lazada order', ['order_id' => $orderId]);

        try {
            $response = Http::get($url,$params);

            $data = json_decode($response->getBody()->getContents(), true);

            DB::beginTransaction();
            $customer = $sale->customer;
            $customer->update([
                'name' => $data['data']['customer_first_name'].$data['data']['customer_last_name']
            ]);


            $shipAddress = $data['data']['address_shipping']['address1']
                    . $data['data']['address_shipping']['address2']
                    . $data['data']['address_shipping']['address3']
                    . $data['data']['address_shipping']['address4']
                    . $data['data']['address_shipping']['address5'];

            $existingShippingAddress = CustomerLocation::where('customer_id', $customer->id)
                ->where('type', 2) 
                ->where('address', $shipAddress)
                ->where('city', $data['data']['address_shipping']['city'])
                ->where('state', $data['data']['address_shipping']['country']) 
                ->where('zip_code', $data['data']['address_shipping']['post_code'])
                ->first();

            if (!$existingShippingAddress) {   
                $shippingAddress = CustomerLocation::create([
                    'customer_id' => $customer->id,
                    'type' => 2, 
                    'is_default' => 1,
                    'address' => $shipAddress,
                    'city' => $data['data']['address_shipping']['city'],
                    'state' => $data['data']['address_shipping']['country'], 
                    'zip_code' => $data['data']['address_shipping']['post_code']
                ]);

                (new Branch())->assign(CustomerLocation::class, $shippingAddress->id, Branch::LOCATION_KL);

                Log::info('Created new shipping address', ['customer_id' => $customer->id, 'address' => $shipAddress]);
            } else {
                $shippingAddress = $existingShippingAddress;
            }

            $billAddress = $data['data']['address_billing']['address1']
                . $data['data']['address_billing']['address2']
                . $data['data']['address_billing']['address3']
                . $data['data']['address_billing']['address4']
                . $data['data']['address_billing']['address5'];

            $existingBillingAddress = CustomerLocation::where('customer_id', $customer->id)
                ->where('type', 1) 
                ->where('address', $billAddress)
                ->where('city', $data['data']['address_billing']['city'])
                ->where('state', $data['data']['address_billing']['address2']) 
                ->where('zip_code', $data['data']['address_billing']['post_code'])
                ->first();

            if (!$existingBillingAddress) {              
                $customerLocation = CustomerLocation::create([
                    'customer_id' => $customer->id,
                    'type' => 1, 
                    'is_default' => 1,
                    'address' => $billAddress,
                    'city' => $data['data']['address_billing']['city'],
                    'state' => $data['data']['address_billing']['address2'], 
                    'zip_code' => $data['data']['address_billing']['post_code']
                ]);

                (new Branch())->assign(CustomerLocation::class, $customerLocation->id, Branch::LOCATION_KL);

                Log::info('Created new billing address', ['customer_id' => $customer->id, 'address' => $billAddress]);
            }


            $sale->update([
                'reference' => json_encode([$data['data']['buyer_note']]),
                'remark'=> $data['data']['remarks'],
                'payment_method'=>  $data['data']['payment_method'],
                'payment_amount'=> $data['data']['price'],
                'delivery_address_id'=> $shippingAddress->id
            ]);

            Log::info('Updated sale record', ['sale_id' => $sale->id, 'order_id' => $orderId]);
            
            DB::commit();
            return response()->json(['message' => 'Webhook received and processed successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to fetch order data from Lazada', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'response' => $response->json()
            ]);
            return response()->json(['error' => 'Failed to fetch data from Lazada API', 'message' => $e->getMessage()], 500);
        }

    }


    public function getLazadaOrderItems($orderId,$status){
        $url = $this->endpoint.'/order/items/get';
        $appKey = $this->appKey;
        $appSecret = $this->appSecret; 
        $params = [
            'app_key' => $appKey,
            'timestamp' => time() * 1000,
            'access_token' => $this->accessToken,
            'sign_method' => 'sha256',
            'order_id' => $orderId,
        ];
        $params['sign'] = $this->generateSignature($params, $appSecret, '/order/items/get');

        Log::info('Fetching Lazada order items', ['order_id' => $orderId]);

        try {
            $response = Http::get($url,$params);
            $data = json_decode($response->getBody()->getContents(), true);

            $skuMap = [];
            foreach ($data['data'] as $item) {
                if (isset($skuMap[$item['sku']])) {
                    $skuMap[$item['sku']]['qty'] += 1;
                } else {
                    $skuMap[$item['sku']] = [
                        'item' => $item,
                        'qty' => 1
                    ];
                }
            }

            DB::beginTransaction();
            foreach ($skuMap as $skuData) {
                $item = $skuData['item'];
                $quantity = $skuData['qty'];

                $customer = Customer::where('sku', $item['buyer_id'])->where('platform_id',$this->platform->id)->first();
                if (!$customer) {
                    $customer = Customer::create([
                        'sku' => $item['buyer_id'],
                        'platform_id' => $this->platform->id
                    ]);

                    (new Branch())->assign(Customer::class, $customer->id, Branch::LOCATION_KL);

                    Log::info('Created new customer', ['sku' => $item['buyer_id'], 'platform' => $this->platform->name]);
                }

                $sale = Sale::create([
                    'order_id' => $orderId,
                    'status' => $status,
                    'platform_id' => $this->platform->id,
                    'sku' => $orderId,
                    'type' => Sale::TYPE_PENDING,
                    'customer_id' => $customer->id,
                ]);

                Log::info('Created new sale record', ['sale_id' => $sale->id, 'order_id' => $orderId]);

                (new Branch())->assign(Sale::class, $sale->id, Branch::LOCATION_KL);

                $product = Product::where('lazada_sku', $item['sku'])->first();

                $saleProduct = SaleProduct::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'desc' => $item['name'] ?? null,
                    'qty' => $quantity, 
                    'unit_price' => $item['paid_price'] ?? 0
                ]);

                (new Branch())->assign(SaleProduct::class, $saleProduct->id, Branch::LOCATION_KL);

                Log::info('Added sale product', ['sale_id' => $sale->id, 'product_id' => $product->id]);
            }
            DB::commit();
            Log::info('Successfully processed Lazada order items', ['order_id' => $orderId]);
            return $this->getLazadaOrder($orderId,$sale);
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to fetch Lazada order items', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'response' => $response->json()
            ]);
            
            return response()->json(['error' => 'Failed to fetch data from Lazada API', 'message' => $e->getMessage()], 500);
        }
    }


    private function generateSignature($params, $appSecret, $apiName) {
        ksort($params);
    
        $queryString = '';
        foreach ($params as $key => $value) {
            $queryString .= $key . $value;
        }
    
        $stringToSign = $apiName . $queryString;
        
        $hmac = hash_hmac('sha256', $stringToSign,$appSecret, true);

        return strtoupper(bin2hex($hmac));
    }
}
