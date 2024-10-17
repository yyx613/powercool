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

    public function __construct()
    {
        $this->partnerId = config('platforms.shopee.partner_id');
        $this->partnerKey = config('platforms.shopee.partner_key');
        $this->shopId = config('platforms.shopee.shop_id');
        $this->endpoint = 'https://partner.test-stable.shopeemobile.com';
        $this->accessToken = PlatformTokens::where('platform','Shopee')->first()->access_token;
    }

    public function getAccessTokenShopee(Request $request)
    {
        // 参数配置
        $code = '5a5477794a55537954697169514f4653'; // 替换为授权步骤中获取的 code
        $partnerId = $this->partnerId; // 替换为你的 partner_id
        $partnerKey = $this->partnerKey; // 替换为你的 partner_key

        // 当前时间戳（注意是 Unix 时间戳）
        $timestamp = time();

        // 生成签名
        $sign = hash_hmac('sha256', $partnerId . '/api/v2/auth/token/get' . $timestamp, $partnerKey);

        // 发送 POST 请求到 Shopee API
        try {
            $response = Http::post('https://partner.shopeemobile.com/api/v2/auth/token/get', [
                'code' => $code,
                'partner_id' => $partnerId,
                'partner_id' => $partnerId,
                'timestamp' => $timestamp,
                'sign' => $sign,
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            // 检查响应
            dd($data);
            if ($response->successful()) {
                // 返回 access_token 和其他信息
                return response()->json([
                    'access_token' => $response['access_token'],
                    'refresh_token' => $response['refresh_token'],
                    'expire_in' => $response['expire_in'],
                ]);
            }
    
            // 返回错误信息
            return response()->json([
                'error' => $response['error'],
                'message' => $response['message'],
            ], $response->status());
        } catch (\Throwable $th) {
            dd($th);
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
        // 获取请求数据
        $data = $request->input('data');

        // 验证数据是否存在
        if (!$data) {
            return response()->json(['message' => 'No data provided'], 400);
        }

        // 处理订单状态
        $orderSN = $data['ordersn'] ?? null;
        $status = $data['status'] == 'CANCELLED' || $data['status'] == 'UNPAID' || $data['status'] == 'TO RETURN' ? Sale::STATUS_INACTIVE : Sale::STATUS_ACTIVE;
        $completedScenario = $data['completed_scenario'] ?? null;
        $updateTime = $data['update_time'] ?? null;
        $shopId = $request->input('shop_id'); // 从请求中获取 shop_id

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
        $url = $this->endpoint.'/api/v2/order/get_order_detail';

         $params = [
            'partner_id' => $this->partnerId,
            'timestamp' => time() * 1000, 
            'access_token' => $this->accessToken,
            'shop_id' => $this->shopId,
            'sign' => 'your_generated_sign',
            'order_sn_list' => $orderId, 
        ];

        try {
            $response = Http::get($url,$params);

            $data = json_decode($response->getBody()->getContents(), true);

            DB::beginTransaction();
            foreach ($data['response']['order_list'] as $order) {
                $customer = Customer::where('sku', $order['buyer_user_id'])->where('platform','Shopee')->first();
                if(!$customer){
                    $customer = Customer::create([
                        'sku' => $order['buyer_user_id'],
                        'name' => $order['buyer_username'],
                        'platform' => 'Shopee'
                    ]);
                }

                $shippingAddress = CustomerLocation::create([
                    'customer_id' => $customer->id,
                    'type' => 2, 
                    'is_default' => 1,
                    'address' => $order['recipient_address']['full_address'],
                    'city' => $order['recipient_address']['city'],
                    'state' => $order['recipient_address']['state'], 
                    'zip_code' => $order['recipient_address']['zipcode']
                ]);


                $sale = Sale::create([
                    'order_id' => $orderId,
                    'status' => $status,
                    'platform' => 'Shopee',
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
            DB::rollBack();
            return response()->json(['error' => 'Failed to fetch data from Lazada API', 'message' => $e->getMessage()], 500);
        }

    }
}
