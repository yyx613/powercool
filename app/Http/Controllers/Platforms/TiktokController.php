<?php

namespace App\Http\Controllers\Platforms;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\PlatformTokens;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TiktokController extends Controller
{
    protected $endpoint;
    protected $appKey;
    protected $appSecret;
    protected $accessToken;
    protected $platform = 'Tiktok';


    public function __construct()
    {
        $this->appKey = config('platforms.tiktok.app_key');
        $this->appSecret = config('platforms.tiktok.app_secret');
        $this->endpoint = 'https://open-api.tiktokglobalshop.com';
        $this->accessToken = PlatformTokens::where('platform',$this->platform)->first()->access_token;
    }

    public function handleTiktokWebhook(Request $request)
    {
        $data = $request->input('data');

        if (!$data) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $orderId = $data['order_id'] ?? null;
        $status = $data['order_status'] == 'CANCELLED' || $data['order_status'] == 'UNPAID' ?  Sale::STATUS_INACTIVE : Sale::STATUS_ACTIVE;

        $sale = Sale::where('order_id',$orderId)->first();
        if($sale){
            $sale->update([
                'status' =>  $status 
            ]);
        }else{
            $this->getTiktokOrder($orderId,$status);
        }

        return response()->json(['message' => 'Webhook received successfully'], 200);
    }


    private function generateSignature($params, $appSecret, $apiName)
    {
        unset($params['sign']);
        unset($params['access_token']);
        ksort($params);

        $signString = $appSecret . $apiName; 
        foreach ($params as $key => $value) {
            $signString .= $key . $value;
        }

        $signString .= $appSecret; 

        $signature = hash_hmac('sha256', $signString, $appSecret);

        // Return the generated sign
        return response()->json(['sign' => $signature]);
    }

    public function getAccessTokenTiktok($code){
        $url = 'https://auth.tiktok-shops.com/api/v2/token/get';

        $params = [
            'query' => [
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
                'auth_code' => $code,
                'grant_type' => 'authorized_code',
            ],
        ];

        try {
            $response = Http::get($url, $params);
            if ($response->successful()) {
                DB::beginTransaction();

                $responseData = $response->json()['data'];
                PlatformTokens::updateOrCreate(
                    ['platform' => $this->platform],
                    [
                        'access_token' => $responseData['access_token'],
                        'refresh_token' => $responseData['refresh_token'],
                        'access_token_expires_at' => Carbon::createFromTimestamp($responseData['access_token_expire_in']),
                        'refresh_token_expires_at' => Carbon::createFromTimestamp($responseData['refresh_token_expire_in']),
                    ]
                );

                DB::commit();
                return 'success';
            } else {
                return response()->json([
                    'error' => 'Failed to get token',
                    'message' => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to fetch access token'], 500);
        }
    }

    public function refreshTiktokAccessToken()
    {
        $platformToken = PlatformTokens::where('platform', $this->platform)->first();

        $url = 'https://auth.tiktok-shops.com/api/v2/token/refresh';
        $queryParams = [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret,
            'refresh_token' => $platformToken->refresh_token,
            'grant_type' => 'refresh_token'
        ];

        try {
            $response = Http::get($url, $queryParams);

            if ($response->successful()) {
                DB::beginTransaction();

                $responseData = $response->json()['data'];
                $platformToken->access_token = $responseData['access_token'];
                $platformToken->refresh_token = $responseData['refresh_token'];
                $platformToken->access_token_expires_at = Carbon::createFromTimestamp($responseData['access_token_expire_in']);
                $platformToken->refresh_token_expires_at = Carbon::createFromTimestamp($responseData['refresh_token_expire_in']);
                $platformToken->save();

                DB::commit();
                return 'success';
            } else {
                return response()->json([
                    'error' => 'Failed to refresh token',
                    'message' => $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getTiktokOrder($orderId,$status){
        $url = $this->endpoint.'/api/orders/detail/query';

        $params = [
            'app_key' => $this->appKey,
            'timestamp' => time() * 1000, 
            'access_token' => $this->accessToken,
            'order_id_list' => $orderId, 
        ];

        $params['sign'] = $this->generateSignature($params, $this->appSecret, '/api/orders/detail/query');

        try {
            $response = Http::get($url,$params);

            $data = json_decode($response->getBody()->getContents(), true);

            DB::beginTransaction();
            foreach ($data['data']['order_list'] as $order) {
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
                foreach ($skuMap as $skuData) {
                    $item = $skuData['item'];
                    $quantity = $skuData['qty'];
                    $customer = Customer::where('sku', $item['buyer_uid'])->where('platform','Tiktok')->first();
                    if(!$customer){
                        $customer = Customer::create([
                            'sku' => $item['buyer_uid'],
                            'email' => $item['buyer_email'],
                            'platform' => 'Tiktok'
                        ]);
                    }
                    $existingShippingAddress = CustomerLocation::where('customer_id', $customer->id)
                        ->where('type', 2) 
                        ->where('address', $item['recipient_address']['full_address'])
                        ->where('city', $item['recipient_address']['city'])
                        ->where('state', $item['recipient_address']['state']) 
                        ->where('zip_code', $item['recipient_address']['zipcode'])
                        ->first();

                    if (!$existingShippingAddress) {
                        $shippingAddress = CustomerLocation::create([
                            'customer_id' => $customer->id,
                            'type' => 2, 
                            'is_default' => 1,
                            'address' => $item['recipient_address']['full_address'],
                            'city' => $item['recipient_address']['city'],
                            'state' => $item['recipient_address']['state'], 
                            'zip_code' => $item['recipient_address']['zipcode']
                        ]);
                    }else {
                        $shippingAddress = $existingShippingAddress;
                    }

                    $sale = Sale::create([
                        'order_id' => $orderId,
                        'status' => $status,
                        'platform' => 'Tiktok',
                        'sku' => $orderId,
                        'payment_amount'=> $item['total_amount'],
                        'payment_method'=> $item['payment_method_name'],
                        'delivery_address_id'=> $shippingAddress->id,
                        'remark' => $item['buyer_message']
                    ]);

                    $product = product::where('tiktok_sku', $item['seller_sku'])->first();
                    SaleProduct::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'desc' => $item['product_name'] ?? null,
                        'qty' => $quantity, 
                        'unit_price' => $item['sale_price'] ?? 0,
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to fetch data from Lazada API', 'message' => $e->getMessage()], 500);
        }

    }
}
