<?php

namespace App\Http\Controllers\Platforms;

use App\Http\Controllers\Controller;
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

class ShopeeController extends Controller
{
    protected $endpoint;
    protected $partnerId;
    protected $partnerKey;
    protected $accessToken;
    protected $shopId;
    protected $platform = 'Shopee';

    public function __construct()
    {
        $this->partnerId = (int) config('platforms.shopee.partner_id');
        $this->partnerKey = config('platforms.shopee.partner_key');
        $this->shopId = (int) config('platforms.shopee.shop_id');
        $this->endpoint = 'https://partner.test-stable.shopeemobile.com';
        $this->accessToken = PlatformTokens::where('platform',$this->platform)->first()->access_token;
    }

    public function getAccessTokenShopee($code)
    {
        $partnerId = $this->partnerId; 
        $partnerKey = $this->partnerKey; 
        $timestamp = time();
        $path = '/api/v2/auth/token/get';
        $url = $this->endpoint.$path;

        $bodyParams = [
            'shop_id' => $this->shopId,
            'code' => $code,
            'partner_id' => $partnerId
        ];

        $baseString = sprintf("%s%s%s", $partnerId, $path, $timestamp);
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        $queryParams = [
            'partner_id' => $partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url . '?' . http_build_query($queryParams), $bodyParams);

            $responseData = $response->json();
            DB::beginTransaction();
            PlatformTokens::updateOrCreate(
                ['platform' => $this->platform],
                [
                    'access_token' => $responseData['access_token'],
                    'refresh_token' => $responseData['refresh_token'],
                    'access_token_expires_at' => Carbon::now()->addSeconds($responseData['expire_in']),
                    'refresh_token_expires_at' => Carbon::now()->addDays(30),
                ]
            );
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function refreshAccessTokenShopee()
    {
        $platformToken = PlatformTokens::where('platform', $this->platform)->first();
        $refreshToken = $platformToken->refresh_token;
        
        $path = "/api/v2/auth/access_token/get";
        $timestamp = time();
        
        $body = [
            'partner_id' => $this->partnerId,
            'refresh_token' => $refreshToken,
            'shop_id' => $this->shopId
        ];
        $baseString = sprintf("%s%s%s", $this->partnerId, $path, $timestamp);
        $sign = hash_hmac('sha256', $baseString, $this->partnerKey);

        $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", $this->endpoint, $path, $this->partnerId, $timestamp, $sign);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($url, $body);

            
            if ($response->successful()) {
                DB::beginTransaction();

                $responseData = $response->json();
                $platformToken->access_token = $responseData['access_token'];
                $platformToken->refresh_token = $responseData['refresh_token'];
                $platformToken->access_token_expires_at = Carbon::now()->addSeconds($responseData['expire_in']);
                $platformToken->refresh_token_expires_at = Carbon::now()->addDays(30); 
                $platformToken->save();

                DB::commit();
                return 'success';
            } else {
                return response()->json([
                    'error' => 'Request failed',
                    'message' => $response->body(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($responseData);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function generateAuthLinkShopee()
    {
        // 参数配置
        $partnerId = $this->partnerId; // 替换为你的 partner_id
        $partnerKey = $this->partnerKey; // 替换为你的 partner_key
        $redirectUrl = 'https://powercool.at-eases.com'; // 替换为你的重定向URL
        $host = 'https://partner.test-stable.shopeemobile.com';
        $path = '/api/v2/shop/auth_partner';

        // 获取当前的 Unix 时间戳
        $timestamp = time();

        // 生成 base string
        $baseString = sprintf('%s%s%s', $partnerId, $path, $timestamp);

        // 计算签名 sign
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        // 生成授权链接
        $authUrl = sprintf(
            '%s%s?partner_id=%s&timestamp=%s&sign=%s&redirect=%s',
            $host,
            $path,
            $partnerId,
            $timestamp,
            $sign,
            urlencode($redirectUrl)
        );

        // 打印或记录 URL
        // Log::info('Shopee Authorization URL: ' . $authUrl);

        // 返回授权链接
        return response()->json([
            'auth_url' => $authUrl,
        ]);
    }

    public function handleShopeeWebhook(Request $request)
    {
        $data = $request->input('data');

        if (!$data) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        $orderSN = $data['ordersn'] ?? null;
        $status = $data['status'] == 'CANCELLED' || $data['status'] == 'UNPAID' || $data['status'] == 'TO RETURN' ? Sale::STATUS_INACTIVE : Sale::STATUS_ACTIVE;
        $completedScenario = $data['completed_scenario'] ?? null;
        $updateTime = $data['update_time'] ?? null;
        $shopId = $request->input('shop_id');
        // $this->getShopeeOrderList();
        $sale = Sale::where('order_id',$orderSN)->first();
        if($sale){
            $sale->update([
                'status' =>  $status
            ]);
        }else{
            $this->getShopeeOrder($orderSN,$status);
        }

        return response()->json(['message' => 'Webhook received successfully'], 200);
    }

    private function getShopeeOrder($orderId,$status){
        $path = '/api/v2/order/get_order_detail';
        $url = $this->endpoint.$path;
        $timestamp = time();

        $params = [
            'partner_id' => $this->partnerId,
            'timestamp' =>  $timestamp, 
            'access_token' => $this->accessToken,
            'shop_id' => $this->shopId,
            'order_sn_list' => $orderId, 
        ];

        $baseString = sprintf("%s%s%s%s%s", $this->partnerId, $path, $timestamp, $this->accessToken, $this->shopId);
        $params['sign'] = hash_hmac('sha256', $baseString, $this->partnerKey);

        try {
            $response = Http::get($url,$params);

            $data = json_decode($response->getBody()->getContents(), true);
            DB::beginTransaction();
            foreach ($data['response']['order_list'] as $order) {
                $customer = Customer::where('sku', $order['buyer_user_id'])->where('platform',$this->platform)->first();
                if(!$customer){
                    $customer = Customer::create([
                        'sku' => $order['buyer_user_id'],
                        'name' => $order['buyer_username'],
                        'platform' => $this->platform
                    ]);
                }
                $existingShippingAddress = CustomerLocation::where('customer_id', $customer->id)
                    ->where('type', 2) 
                    ->where('address', $order['recipient_address']['full_address'])
                    ->where('city',$order['recipient_address']['city'])
                    ->where('state', $order['recipient_address']['state']) 
                    ->where('zip_code', $order['recipient_address']['zipcode'])
                    ->first();
                if (!$existingShippingAddress) {
                    $shippingAddress = CustomerLocation::create([
                        'customer_id' => $customer->id,
                        'type' => 2, 
                        'is_default' => 1,
                        'address' => $order['recipient_address']['full_address'],
                        'city' => $order['recipient_address']['city'],
                        'state' => $order['recipient_address']['state'], 
                        'zip_code' => $order['recipient_address']['zipcode']
                    ]);
                }else {
                    $shippingAddress = $existingShippingAddress;
                }


                $sale = Sale::create([
                    'order_id' => $orderId,
                    'status' => $status,
                    'platform' => $this->platform,
                    'sku' => $orderId,
                    'type' => 2,
                    'customer_id' => $customer->id,
                    'payment_amount'=> $order['total_amount'],
                    'payment_method'=> $order['payment_method'],
                    'delivery_address_id'=> $shippingAddress->id,
                    'remark' => $order['message_to_seller'],
                    'reference' => json_encode([$order['note']])
                ]);
              

                foreach ($order['item_list'] as $item) {
                    $product = Product::where('shopee_sku', $item['model_sku'])->first();
                    SaleProduct::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'desc' => $item['item_name'] ?? null,
                        'qty' => $item['model_quantity_purchased'], 
                        'unit_price' => $item['model_discounted_price'] ?? 0,
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return response()->json(['error' => 'Failed to fetch data from Lazada API', 'message' => $e->getMessage()], 500);
        }

    }

    private function getShopeeOrderList(){
        $path = '/api/v2/order/get_order_list';
        $url = $this->endpoint.$path;
        $timestamp = time();

        $params = [
            'partner_id' => $this->partnerId,
            'timestamp' =>  $timestamp, 
            'access_token' => $this->accessToken,
            'shop_id' => $this->shopId,
            'time_range_field' => 'create_time', // 时间范围字段，指定是基于创建时间
            'time_from' => Carbon::now()->subDays(15)->timestamp, // 查询从15天前开始
            'time_to' => Carbon::now()->timestamp, // 查询到现在为止
            'page_size' => 20, // 每页显示20条订单
        ];
        $baseString = sprintf("%s%s%s%s%s", $this->partnerId, $path, $timestamp, $this->accessToken, $this->shopId);
        $params['sign'] = hash_hmac('sha256', $baseString, $this->partnerKey);

        // $url = sprintf("https://partner.shopeemobile.com%s?partner_id=%s&timestamp=%s&sign=%s&shop_id=%s&access_token=%s", 
        // $path, $this->partnerId, $timestamp, $params['sign'], $this->shopId, $this->accessToken);
        
        try {
            // 发送 GET 请求
            
            $response = Http::get($url, $params);
            dd($response->json());
            // 检查响应是否成功
            if ($response->successful()) {
                $responseData = $response->json();
                return response()->json($responseData);
            } else {
                return response()->json([
                    'error' => 'Failed to fetch Shopee orders',
                    'message' => $response->body()
                ], $response->status());
            }
            
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}