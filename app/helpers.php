<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

if (!function_exists('generateRandomAlphabet')) {
    function generateRandomAlphabet($length = 5) {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    function getUserRole(User $user) {
        return $user->getRoleNames()[0] ?? null;
    }
    
    function getUserRoleId(User $user) {
        return $user->roles->pluck('id')[0] ?? null;
    }
}