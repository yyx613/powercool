<?php

namespace App\Http\Controllers\Platforms;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\PlatformTokens;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LazadaController extends Controller
{
    protected $appKey; 
    protected $appSecret; 
    protected $endpoint;
    protected $accessToken;
    protected $platform = 'Lazada';

    public function __construct()
    {
        $this->appKey = config('platforms.lazada.app_key');
        $this->appSecret = config('platforms.lazada.secret_key');
        $this->endpoint = 'https://api.lazada.com.my/rest';
        $this->accessToken = PlatformTokens::where('platform',$this->platform)->first()->access_token;
    }

    public function handleLazadaWebhook(Request $request)
    {
        $messageBody = $request->getContent(); 

        $appKey = $this->appKey; 
        $appSecret = $this->appSecret; 

        $receivedSignature = $request->header('Authorization');

        $base = $appKey . $messageBody;

        $expectedSignature = $this->generateWebhookSignature($base, $appSecret);

        // if (!hash_equals($expectedSignature, $receivedSignature)) {
        //     return response()->json(['message' => 'Invalid signature'], 401);
        // }

        $data = json_decode($messageBody, true); 
        $orderId = $data['data']['trade_order_id'];
        $status = ($data['data']['order_status'] == 'returned' || $data['data']['order_status'] == 'unpaid' || $data['data']['order_status'] == 'cancelled') ? Sale::STATUS_INACTIVE : Sale::STATUS_ACTIVE;
        try {
            $sale = Sale::where('order_id',$orderId)->first();
            if($sale){
                $sale->update([
                    'status' =>  $status
                ]);
            }else{

                $this->getLazadaOrderItems($orderId,$status);
            }        
        } catch (\Throwable $th) {
            dd($th);
        }
       


        return response()->json(['message' => 'Webhook received and processed successfully'], 200);
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

        $params['sign'] = $this->generateSignature($params, $appSecret, '/auth/token/create');
        try {
            $response = Http::get($endpoint, $params);
            if ($response->successful()) {
                $data = $response->json();

                $newAccessToken = $data['access_token'];
                $newRefreshToken = $data['refresh_token'];
                $expiresIn = $data['expires_in'];
                $refreshExpiresIn = $data['refresh_expires_in'];
                DB::beginTransaction();
                PlatformTokens::updateOrCreate(
                    ['platform' => $this->platform],
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
            return response()->json(['error' => 'Failed to fetch data from Lazada API', 'message' => $e->getMessage()], 500);
        }
        
    }

    public function refreshAccessTokenLazada()
    {
        $appKey = $this->appKey;
        $appSecret = $this->appSecret;
        $refreshToken = PlatformTokens::where('platform',$this->platform)->first()->refresh_token;
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
    
                $newAccessToken = $data['access_token'];
                $newRefreshToken = $data['refresh_token'];
                $expiresIn = $data['expires_in'];
                $refreshExpiresIn = $data['refresh_expires_in'];
                DB::beginTransaction();
                PlatformTokens::where('platform', 'Lazada')->update([
                    'access_token' => $newAccessToken,
                    'refresh_token' => $newRefreshToken,
                    'access_token_expires_at' => Carbon::now()->addSeconds($expiresIn),
                    'refresh_token_expires_at' => Carbon::now()->addSeconds($refreshExpiresIn),
                ]);
                DB::commit();
                return 'success';
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
                CustomerLocation::create([
                    'customer_id' => $customer->id,
                    'type' => 1, 
                    'is_default' => 1,
                    'address' => $billAddress,
                    'city' => $data['data']['address_billing']['city'],
                    'state' => $data['data']['address_billing']['address2'], 
                    'zip_code' => $data['data']['address_billing']['post_code']
                ]);
            }


            $sale->update([
                'reference' => json_encode([$data['data']['buyer_note']]),
                'remark'=> $data['data']['remarks'],
                'payment_method'=>  $data['data']['payment_method'],
                'payment_amount'=> $data['data']['price'],
                'delivery_address_id'=> $shippingAddress->id
            ]);
            
            DB::commit();
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
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

                $customer = Customer::where('sku', $item['buyer_id'])->where('platform',$this->platform)->first();
                if (!$customer) {
                    $customer = Customer::create([
                        'sku' => $item['buyer_id'],
                        'platform' => $this->platform
                    ]);
                }

                $sale = Sale::create([
                    'order_id' => $orderId,
                    'status' => $status,
                    'platform' => $this->platform,
                    'sku' => $orderId,
                    'type' => Sale::TYPE_PENDING,
                    'customer_id' => $customer->id,
                ]);

                (new Branch())->assign(Sale::class, $sale->id, Branch::LOCATION_KL);

                $product = Product::where('lazada_sku', $item['sku'])->first();

                SaleProduct::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'desc' => $item['name'] ?? null,
                    'qty' => $quantity, 
                    'unit_price' => $item['paid_price'] ?? 0
                ]);
            }
            DB::commit();
            $this->getLazadaOrder($orderId,$sale);
        }catch (\Exception $e) {
            dd($e);
            DB::rollBack();
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
