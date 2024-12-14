<?php

namespace App\Console\Commands;

use App\Models\EInvoice;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CheckEInvoiceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-e-invoice-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    protected $endpoint;
    protected $powerCoolId;
    protected $powerCoolSecret;
    protected $hitenId;
    protected $hitenSecret;
    protected $accessTokenPowerCool;
    protected $accessTokenHiten;

    public function __construct()
    {
        parent::__construct();
        $this->powerCoolId = config('e-invoices.powercool_client_id');
        $this->powerCoolSecret = config('e-invoices.powercool_client_secret');
        $this->hitenId = config('e-invoices.hiten_client_id');
        $this->hitenSecret = config('e-invoices.hiten_client_secret');
        $this->endpoint = 'https://preprod-api.myinvois.hasil.gov.my';
        $this->accessTokenPowerCool = $this->getAccessToken('powercool');
        $this->accessTokenHiten = $this->accessTokenPowerCool;
    }

    public function getAccessToken($company)
    {
        $cacheKey = "access_token_{$company}";
        $accessTokenData = Cache::get($cacheKey);
        
        if ($accessTokenData) {
            $expiresAt = $accessTokenData['expires_at'];
            if (now()->addMinute()->lt($expiresAt)) {
                return $accessTokenData['access_token'];
            }
        }

        $response = $this->login($company);


        if ($response->status() === 200) {
            $accessToken = $response->getData()->access_token;
            $expiresIn = $response->getData()->expires_in;
            
            $expiresAt = now()->addSeconds($expiresIn);
            Cache::put($cacheKey, [
                'access_token' => $accessToken,
                'expires_at' => $expiresAt
            ], $expiresAt);

            return $accessToken;
        }

        return null;
    }

    public function login($company)
    {
        try {
            $path = "/connect/token";
            $url = $this->endpoint . $path;

            $clientId = $company == "powercool" ? $this->powerCoolId : $this->hitenId;
            $clientSecret = $company == "powercool" ? $this->powerCoolSecret : $this->hitenSecret;

            $response = Http::asForm()->post($url, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'client_credentials',
                'scope' => 'InvoicingAPI',
            ]);

            if ($response->successful()) {
                $accessToken = $response->json()['access_token'];
                $expiresIn = $response->json()['expires_in'];

                return response()->json([
                    'access_token' => $accessToken,
                    'expires_in' => $expiresIn,
                ]);
            } else {
                return response()->json([
                    'error' => 'Login failed',
                    'message' => $response->body(),
                ], $response->status());
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred during login',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

     public function handle()
     {
         $cutoffDate = Carbon::now()->subHours(72);
     
         $einvoices = EInvoice::where('submission_date', '>=', $cutoffDate)
             ->whereHasMorph(
                 'einvoiceable', 
                 [Invoice::class], 
                 function ($query) {
                     $query->whereNotNull('company'); 
                 }
             )
             ->get();
             Log::info('einvoice', ['messageBody' => $einvoices]);

         foreach ($einvoices as $einvoice) {
             $company = $einvoice->einvoiceable->company ?? null;
     
             if (!$company) {
                 $this->error("Company not found for UUID {$einvoice->uuid}");
                 continue;
             }
     
             $result = $this->getDocumentDetails($einvoice->uuid, $company);

             if (isset($result['error'])) {
                 $this->error("Error for UUID {$einvoice->uuid}: {$result['details']}");
             } else {
                 $this->info("Successfully updated status for UUID {$einvoice->uuid}");
             }
         }
     }
     

    public function getDocumentDetails($uuid, $company) 
    {
        try {
            $url = "https://preprod-api.myinvois.hasil.gov.my/api/v1.0/documents/{$uuid}/details";

            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . ($company === 'powercool' ? $this->accessTokenPowerCool : $this->accessTokenHiten),
            ];

            $response = Http::withHeaders($headers)->get($url);

            if ($response->successful()) {
                $validationResults = $response->json()['validationResults'] ?? null;
                Log::info('test', ['messageBody' => $response->json()]);

                if ($validationResults) {
                    $einvoice = EInvoice::where('uuid', $uuid)->first();

                    if ($einvoice) {
                        $einvoice->update([
                            'status' => $response->json()['status'],
                        ]);
                    } else {
                        return ['error' => 'EInvoice not found'];
                    }
                }

                return ['success' => true];
            } else {
                return ['error' => 'API request failed', 'details' => $response->body()];
            }
        } catch (\Throwable $th) {
            // Log error for debugging
            Log::error('Error fetching document details', [
                'uuid' => $uuid,
                'company' => $company,
                'error' => $th->getMessage(),
            ]);

            return ['error' => 'Exception occurred', 'details' => $th->getMessage()];
        }
    }

}
