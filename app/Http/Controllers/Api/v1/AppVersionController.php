<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AppVersionController extends Controller
{
    public function check()
    {
        return Response::json([
            'android' => config('app-version.android'),
            'ios' => config('app-version.ios'),
        ], HttpFoundationResponse::HTTP_OK);
    }
}
