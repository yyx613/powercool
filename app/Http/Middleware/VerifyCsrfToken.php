<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'lazada/webhook',
        'shopee/webhook',
        'tiktok/webhook',
        'woo-commerce/order-created/webhook',
        'woo-commerce/order-updated/webhook',
        'test',
        '/mock/document-submission'
    ];
}
