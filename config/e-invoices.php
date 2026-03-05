<?php

$env = env('E_INVOICE_ENV', 'dev');
$suffix = $env === 'live' ? 'LIVE' : 'DEV';

return [
    'env' => $env,
    'endpoint' => $env === 'live'
        ? env('E_INVOICE_LIVE_URL', 'https://api.myinvois.hasil.gov.my')
        : env('E_INVOICE_DEV_URL', 'https://preprod-api.myinvois.hasil.gov.my'),
    'powercool_client_id' => env("E_INVOICE_POWERCOOL_{$suffix}_CLIENT_ID"),
    'powercool_client_secret' => env("E_INVOICE_POWERCOOL_{$suffix}_CLIENT_SECRET"),
    'hiten_client_id' => env("E_INVOICE_HITEN_{$suffix}_CLIENT_ID"),
    'hiten_client_secret' => env("E_INVOICE_HITEN_{$suffix}_CLIENT_SECRET"),
];
