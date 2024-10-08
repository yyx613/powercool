<?php

use App\Models\Milestone;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

if (!function_exists('generateRandomAlphabet')) {
    function generateRandomAlphabet($length = 5)
    {
        return Str::random($length);
    }
}

if (!function_exists('getUserRole')) {
    function getUserRole(User $user)
    {
        return $user->getRoleNames()[0] ?? null;
    }
}

if (!function_exists('getUserRoleId')) {
    function getUserRoleId(User $user)
    {
        return $user->roles->pluck('id')[0] ?? null;
    }
}

if (!function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
        return Auth::user()->hasPermissionTo($permission);
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin()
    {
        return getUserRoleId(Auth::user()) == Role::SUPERADMIN;
    }
}

if (!function_exists('getPaymentCollectionIds')) {
    function getPaymentCollectionIds()
    {
        $ids = Cache::get('payment_collection_ids');

        if ($ids == null || count($ids) <= 0) {
            $ids = Milestone::where('name', 'Payment Collection')->pluck('id')->toArray();

            Cache::put('payment_collection_ids', $ids);
        }

        return $ids;
    }
}

if (!function_exists('priceToWord')) {
    function priceToWord($num = false, $currency = 'myr')
    {
        $num = str_replace(array(',', ' '), '', trim($num));
        if (! $num) {
            return false;
        }
        $ringgit = (int) explode('.', $num)[0];
        $cent = (int) count(explode('.', $num)) > 1 ? explode('.', $num)[1] : 0;
        $list1 = array(
            '',
            'one',
            'two',
            'three',
            'four',
            'five',
            'six',
            'seven',
            'eight',
            'nine',
            'ten',
            'eleven',
            'twelve',
            'thirteen',
            'fourteen',
            'fifteen',
            'sixteen',
            'seventeen',
            'eighteen',
            'nineteen'
        );
        $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
        $list3 = array(
            '',
            'thousand',
            'million',
            'billion',
            'trillion',
            'quadrillion',
            'quintillion',
            'sextillion',
            'septillion',
            'octillion',
            'nonillion',
            'decillion',
            'undecillion',
            'duodecillion',
            'tredecillion',
            'quattuordecillion',
            'quindecillion',
            'sexdecillion',
            'septendecillion',
            'octodecillion',
            'novemdecillion',
            'vigintillion'
        );
        // Ringgit
        $words = array();
        $num_length = strlen($ringgit);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $ringgit = substr('00' . $ringgit, -$max_length);
        $num_levels = str_split($ringgit, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ($tens < 20) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '');
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $words[] = $hundreds . $tens . $singles . (($levels && (int) ($num_levels[$i])) ? ' ' . $list3[$levels] . ' ' : '');
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        $ringgit = implode(' ', $words) . ($currency == 'myr' ? ' ringgit ' : ' dollar ');

        // Cent
        $words = array();
        $num_length = strlen($cent);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $cent = substr('00' . $cent, -$max_length);
        $num_levels = str_split($cent, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ($tens < 20) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '');
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $words[] = $hundreds . $tens . $singles . (($levels && (int) ($num_levels[$i])) ? ' ' . $list3[$levels] . ' ' : '');
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        $cent = implode(' ', $words);
        if ($cent != '') {
            $cent = 'and ' . $cent . ($currency == 'myr' ? ' sen' : ' cent');
        }

        return $ringgit . $cent;
    }
}

