<?php

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

if (!function_exists('generateRandomAlphabet')) {
    function generateRandomAlphabet($length = 5) {
        return Str::random($length);
    }
}

if (!function_exists('getUserRole')) {
    function getUserRole(User $user) {
        return $user->getRoleNames()[0] ?? null;
    }
}

if (!function_exists('getUserRoleId')) {
    function getUserRoleId(User $user) {
        return $user->roles->pluck('id')[0] ?? null;
    }
}

if (!function_exists('getPaymentCollectionIds')) {
    function getPaymentCollectionIds() {
        $ids = Cache::get('payment_collection_ids');

        if ($ids == null || count($ids) <= 0) {
            $ids = Milestone::where('name', 'Payment Collection')->pluck('id')->toArray();

            Cache::put('payment_collection_ids', $ids);
        }

        return $ids;
    }
}