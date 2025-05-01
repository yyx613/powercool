<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Models\PlatformTokens;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshShopeeToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-shopee-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Shopee access token using refresh token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $partnerId = config('platforms.shopee.partner_id');
        $partnerKey = config('platforms.shopee.partner_key');
        $platform = Platform::where('name', 'Shopee')->first();
        $platformToken = PlatformTokens::where('platform_id', $platform->id)->first();
        $refreshToken = $platformToken->refresh_token;
        $shopId = config('platforms.shopee.shop_id'); 
        $timestamp = Carbon::now()->timestamp;

        $url = 'https://partner.shopeemobile.com/api/v2/auth/access_token/get';

        $sign = hash_hmac('sha256', sprintf('%s%s%s', $partnerId, $url, $timestamp), $partnerKey);

        $response = Http::post($url, [
            'partner_id' => $partnerId,
            'refresh_token' => $refreshToken,
            'shop_id' => $shopId,
            'timestamp' => $timestamp,
            'sign' => $sign
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if (empty($data['error'])) {
                $newAccessToken = $data['access_token'];
                $newRefreshToken = $data['refresh_token'];
                $expiresIn = $data['expire_in']; 

                $platformToken->update([
                    'access_token' => $newAccessToken,
                    'refresh_token' => $newRefreshToken,
                    'access_token_expires_at' => Carbon::now()->addSeconds($expiresIn),
                    'refresh_token_expires_at' => Carbon::now()->addDays(30)
                ]);

                Log::info('Shopee access token refreshed successfully.');
            } else {
                Log::error('Error refreshing Shopee token: ' . $data['message']);
            }
        } else {
            Log::error('HTTP request failed with status: ' . $response->status());
        }
    }
}
