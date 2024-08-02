<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

if (!function_exists('generateRandomAlphabet')) {
    function generateRandomAlphabet($length = 5) {
        return Str::random($length);
    }

    function getUserRole(User $user) {
        return $user->getRoleNames()[0] ?? null;
    }
    
    function getUserRoleId(User $user) {
        return $user->roles->pluck('id')[0] ?? null;
    }
}