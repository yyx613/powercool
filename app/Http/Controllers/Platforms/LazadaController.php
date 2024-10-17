<?php

namespace App\Http\Controllers\Platforms;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\PlatformTokens;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LazadaController extends Controller
{
    protected $appKey; 
    protected $appSecret; 
    protected $endpoint;
    protected $accessToken;

    public function __construct()
    {
        $this->appKey = config('platforms.lazada.app_key');
        $this->appSecret = config('platforms.lazada.secret_key');
        $this->endpoint = 'https://api.lazada.com.my/rest';
        $this->accessToken = PlatformTokens::where('platform','Lazada')->first()->access_token;
    }

    public function handleLazadaWebhook(Request $request)
    {
        $messageBody = $request->getContent(); 
        
        $appKey = $this->appKey; 
        $appSecret = $this->appSecret; 

        $receivedSignature = $request->header('Authorization');

        $base = $appKey . $messageBody;

        $expectedSignature = $this->generateWebhookSignature($base, $appSecret);

        if (!hash_equals($expectedSignature, $receivedSignature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $data = json_decode($messageBody, true); 
        $orderId = $data['data']['trade_order_id'];
        $status = $data['data']['order_status'] == 'returned' || $data['data']['order_status'] == 'unpaid' || $data['data']['order_status'] == 'cancelled' ? Sale::STATUS_INACTIVE : Sale::STATUS_ACTIVE;
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
    

    public function getAccessTokenLazada(){
        $endpoint = 'https://auth.lazada.com/rest/auth/token/create';

        $appKey = $this->appKey;
        $appSecret = $this->appSecret;

        $timestamp = round(microtime(true) * 1000);

        $params = [
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'sign_method' => 'sha256',
            'code' => '0_130855_o7RapWchb7giAy8K7gaxbfgz23745', 
        ];

        $params['sign'] = $this->generateSignature($params, $appSecret, '/auth/token/create');
        $response = Http::get($endpoint, $params);

        return $response->json();
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
            .$data['data']['address_shipping']['address2']
            .$data['data']['address_shipping']['address3']
            .$data['data']['address_shipping']['address4']
            .$data['data']['address_shipping']['address5'];
            $shippingAddress = CustomerLocation::create([
                'customer_id' => $customer->id,
                'type' => 2, 
                'is_default' => 1,
                'address' => $shipAddress,
                'city' => $data['data']['address_shipping']['city'],
                'state' => $data['data']['address_shipping']['country'], 
                'zip_code' => $data['data']['address_shipping']['post_code']
            ]);

            $billAddress = $data['data']['address_shipping']['address1']
            .$data['data']['address_billing']['address2']
            .$data['data']['address_billing']['address3']
            .$data['data']['address_billing']['address4']
            .$data['data']['address_billing']['address5'];
            CustomerLocation::create([
                'customer_id' => $customer->id,
                'type' => 1, 
                'is_default' => 1, 
                'address' => $billAddress,
                'city' => $data['data']['address_billing']['city'],
                'state' => $data['data']['address_billing']['address2'], 
                'zip_code' => $data['data']['address_billing']['post_code']
            ]);


            $sale->update([
                'reference' => json_encode([$data['data']['buyer_note']]),
                'remark'=> $data['data']['remarks'],
                'payment_method'=>  $data['data']['payment_method'],
                'payment_amount'=> $data['data']['price'],
                'delivery_address_id'=> $shippingAddress->id
            ]);
            
            DB::commit();
        } catch (\Exception $e) {
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

                $customer = Customer::where('sku', $item['buyer_id'])->where('platform','Lazada')->first();
                if (!$customer) {
                    $customer = Customer::create([
                        'sku' => $item['buyer_id'],
                        'platform' => 'Lazada'
                    ]);
                }

                $sale = Sale::create([
                    'order_id' => $orderId,
                    'status' => $status,
                    'platform' => 'Lazada',
                    'sku' => $item['sku'] ?? null,
                    'type' => 2,
                    'customer_id' => $customer->id,
                    'delivery_instruction' => $item['delivery_option_sof'] ?? null,
                ]);

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
