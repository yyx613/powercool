<?php

namespace App\Console\Commands;

use App\Models\PlatformTokens;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshTikTokToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-tik-tok-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Tiktok access token using refresh token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appKey = config('platforms.tiktok.app_key');
        $appSecret = config('platforms.tiktok.app_secret');
        $token = PlatformTokens::where('platform','Tiktok')->first();
        $refreshToken = $token->refresh_token; 

        // 定义请求 URL
        $url = 'https://auth.tiktok-shops.com/api/v2/token/refresh';

        // 发送请求
        $response = Http::get($url, [
            'app_key' => $appKey,
            'app_secret' => $appSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        // 检查请求是否成功
        if ($response->successful()) {
            $data = $response->json();

            $newAccessToken = $data['data']['access_token'];
            $newRefreshToken = $data['data']['refresh_token'];
            $newAccessTokenTokenExpire = $data['data']['access_token_expire_in'];
            $newRefreshTokenExpire = $data['data']['refresh_token_expire_in'];
            $newAccessTokenExpireAt = Carbon::createFromTimestamp($newAccessTokenTokenExpire)->toDateTimeString();
            $newRefreshTokenExpireAt = Carbon::createFromTimestamp($newRefreshTokenExpire)->toDateTimeString();


            $token->update([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'access_token_expires_at' => $newAccessTokenExpireAt,
                'refresh_token_expires_at' => $newRefreshTokenExpireAt,
            ]);


            Log::info('TikTok access token refreshed successfully.');
        } else {
            Log::error('HTTP request failed with status: ' . $response->status());
        }
    }
}
