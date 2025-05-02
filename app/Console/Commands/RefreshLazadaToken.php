<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Models\PlatformTokens;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshLazadaToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-lazada-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Lazada access token using refresh token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appKey = config('platforms.lazada.app_key');
        $appSecret = config('platforms.lazada.app_secret');
        $platform = Platform::where('name', 'Lazada')->first();
        $platformToken = PlatformTokens::where('platform_id', $platform->id)->first();
        $refreshToken = $platformToken->refresh_token;

        $url = 'https://auth.lazada.com/rest/auth/token/refresh';

        $timestamp = now()->timestamp * 1000; 

        $params = [
            'app_key' => $appKey,
            'refresh_token' => $refreshToken,
            'timestamp' => $timestamp,
            'sign_method' => 'sha256',
        ];

        $sign = $this->generateSign($params, $appSecret);

        $params['sign'] = $sign;

        $response = Http::get($url, $params);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['access_token'])) {
                $newAccessToken = $data['access_token'];
                $newRefreshToken = $data['refresh_token'];
                $expiresIn = $data['expires_in'];
                $refreshExpiresIn = $data['refresh_expires_in'];

                $platformToken->update([
                    'access_token' => $newAccessToken,
                    'refresh_token' => $newRefreshToken,
                    'access_token_expires_at' => Carbon::now()->addSeconds($expiresIn),
                    'refresh_token_expires_at' => Carbon::now()->addSeconds($refreshExpiresIn),
                ]);

                Log::info('Lazada access token refreshed successfully.');
            } else {
                Log::error('Error refreshing Lazada token: ' . json_encode($data));
            }
        } else {
            Log::error('HTTP request failed with status: ' . $response->status());
        }
    }

    private function generateSign($params, $appSecret)
    {
        ksort($params);
        $queryString = '';

        foreach ($params as $key => $value) {
            $queryString .= $key . $value;
        }

        return strtoupper(hash_hmac('sha256', $queryString, $appSecret));
    }
}
