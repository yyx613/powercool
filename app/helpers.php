<?php

use App\Models\Branch;
use App\Models\DeliveryOrderProductChild;
use App\Models\Milestone;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\ProductionMilestone;
use App\Models\ProductionMilestoneMaterial;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Scopes\BranchScope;
use App\Models\TaskMilestoneInventory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

if (! function_exists('generateRandomAlphabet')) {
    function generateRandomAlphabet($length = 5)
    {
        return Str::random($length);
    }
}

if (! function_exists('getUserRole')) {
    function getUserRole(User $user): array
    {
        return $user->load('roles')->roles->pluck('name')->toArray();
    }
}

if (! function_exists('getUserRoleId')) {
    function getUserRoleId(User $user): array
    {
        return $user->load('roles')->roles->pluck('id')->toArray();
    }
}

if (! function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
        if (Auth::user() == null) {
            return false;
        }
        return Auth::user()->hasPermissionTo($permission);
    }
}

if (! function_exists('isSuperAdmin')) {
    function isSuperAdmin(): bool
    {
        return in_array(Role::SUPERADMIN, getUserRoleId(Auth::user()));
    }
}

if (! function_exists('isProductionWorker')) {
    function isProductionWorker(): bool
    {
        return in_array(Role::PRODUCTION_WORKER, getUserRoleId(Auth::user()));
    }
}

if (! function_exists('isSalesOnly')) {
    function isSalesOnly(): bool
    {
        $is_sales_role_only = false;
        if (Auth::user() == null) {
            return false;
        }
        if (count(getUserRoleId(Auth::user())) === 1 && in_array(Role::SALE, getUserRoleId(Auth::user()))) {
            $is_sales_role_only = true;
        }

        return $is_sales_role_only;
    }
}

if (! function_exists('isSalesCoordinatorOnly')) {
    function isSalesCoordinatorOnly(): bool
    {
        $is_sales_role_only = false;
        if (count(getUserRoleId(Auth::user())) === 1 && in_array(Role::SALE_COORDINATOR, getUserRoleId(Auth::user()))) {
            $is_sales_role_only = true;
        }

        return $is_sales_role_only;
    }
}

if (! function_exists('isSalesCoordinator')) {
    function isSalesCoordinator(): bool
    {
        if (in_array(Role::SALE_COORDINATOR, getUserRoleId(Auth::user()))) {
            return true;
        }

        return false;
    }
}

if (! function_exists('isFinanceOnly')) {
    function isFinanceOnly(): bool
    {
        $is_sales_role_only = false;
        if (count(getUserRoleId(Auth::user())) === 1 && in_array(Role::FINANCE, getUserRoleId(Auth::user()))) {
            $is_sales_role_only = true;
        }

        return $is_sales_role_only;
    }
}

if (! function_exists('isFinance')) {
    function isFinance(): bool
    {
        if (in_array(Role::FINANCE, getUserRoleId(Auth::user()))) {
            return true;
        }

        return false;
    }
}

if (! function_exists('getCustomizeProductIds')) {
    function getCustomizeProductIds()
    {
        return Product::where('model_desc', 'like', '%customise%')->withoutGlobalScope(BranchScope::class)->pluck('id');
    }
}

if (! function_exists('getPaymentCollectionIds')) {
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

if (! function_exists('getWhatsAppContent')) {
    function getWhatsAppContent(string $driver_name, string $driver_contact, string $car_plate, string $estimated_time, string $delivery_date)
    {
        $msg = "【Imax Refrigerator's Delivery Details】".'%0a';
        $msg .= 'Dear Valued Customer,'.'%0a';
        $msg .= 'Thank you for your support of Imax Refrigerator Malaysia! We are pleased to inform you that your order has been successfully received. The scheduled delivery date is '.$delivery_date.'.'.'%0a';
        $msg .= '%0a';
        $msg .= 'Please find the delivery details below:'.'%0a';
        $msg .= 'Driver Name: '.$driver_name.'%0a';
        $msg .= 'Contact Number: '.$driver_contact.'%0a';
        $msg .= 'Vehicle Plate Number: '.$car_plate.'%0a';
        $msg .= 'Estimated Time of Arrival (ETA): '.$estimated_time.'%0a';
        $msg .= '%0a';
        $msg .= 'Kindly note that the delivery time is subject to change due to unforeseen circumstances (e.g., heavy traffic, accidents, or other unexpected events). We sincerely appreciate your understanding and thank you for choosing Imax Refrigerator.';
        $msg .= '%0a';
        $msg .= 'Have a wonderful day!';
        $msg .= 'From Hi-Ten Trading Sdn Bhd';
        $msg .= '------------------------------------------------------------------------------------';
        $msg .= '【Imax 商用冰柜运输详情】';
        $msg .= '您好，尊敬的客户：';
        $msg .= '感谢您对大马 Imax 制造商用冰柜的支持！我们已成功收到您的订单，预计送货日期为 '.$delivery_date.'。';
        $msg .= '%0a';
        $msg .= '以下是您的送货详情：'.'%0a';
        $msg .= '司机姓名：'.$driver_name.'%0a';
        $msg .= '联系电话：'.$driver_contact.'%0a';
        $msg .= '车牌号码：'.$car_plate.'%0a';
        $msg .= '预计抵达时间：'.$estimated_time.'%0a';
        $msg .= '预计抵达时间：'.$estimated_time.'%0a';
        $msg .= '%0a';
        $msg .= '请注意，送货时间可能因特殊情况而有所调整（例如：交通阻塞、车祸或其他不可控因素）。感谢您的谅解，并再次感谢您选择 Imax 商用冰柜！';
        $msg .= '%0a';
        $msg .= '祝您生活愉快！'.'%0a';
        $msg .= '来自 Hi-Ten Trading Sdn Bhd'.'%0a';

        // $msg = 'Dear Valued Customer/Mr/Mrs'.'%0a';
        // $msg .= 'We are delighted to inform you that your order with HiTen has been received successfully. The delivery date is on '.$delivery_date.'%0a';
        // $msg .= '%0a';
        // $msg .= 'The details of delivery as below:'.'%0a';
        // $msg .= 'Driver Name: '.$driver_name.'%0a';
        // $msg .= 'Contact Number: '.$driver_contact.'%0a';
        // $msg .= 'Estimate Time Arrival: '.$estimated_time.'%0a';
        // $msg .= 'Car plate: '.$car_plate.'%0a';
        // $msg .= '%0a';
        // $msg .= 'The delivery time may change due to circumstance beyond our control (heavy traffic, accident and etc.'.'%0a';
        // $msg .= 'Appreciate your kind understanding and thanks for shopping with us. Have a nice day! 😊'.'%0a';
        // $msg .= '%0a';
        // $msg .= '中文:'.'%0a';
        // $msg .= '您好Mr/Ms/Mrs,'.'%0a';
        // $msg .= '感谢您对HiTen 的支持! 很高兴让您知道我们已收到您的订单。您的送货期将会在 '.$delivery_date.'%0a';
        // $msg .= '%0a';
        // $msg .= '以下是您的送货详情:'.'%0a';
        // $msg .= '司机姓名:'.$driver_name.'%0a';
        // $msg .= '联系电话:'.$driver_contact.'%0a';
        // $msg .= '抵达时间:'.$estimated_time.'%0a';
        // $msg .= '车牌号码:'.$car_plate.'%0a';
        // $msg .= '送货时间可能因特殊情况而做出临时调整(比如：交通阻塞，车祸或其他特殊情况导致)。'.'%0a';
        // $msg .= '感谢您的谅解以及非常感谢您选择了HiTen 产品';
        //
        return $msg;
    }
}

if (! function_exists('priceToWord')) {
    function priceToWord($num = false, $currency = 'myr')
    {
        $is_negative = false;
        if ($num[0] == '-') {
            $num = str_replace('-', '', $num);
            $is_negative = true;
        }
        $num = str_replace([',', ' '], '', trim($num));
        if (! $num) {
            return false;
        }
        $ringgit = (int) explode('.', $num)[0];
        $cent = (int) count(explode('.', $num)) > 1 ? explode('.', $num)[1] : 0;
        $list1 = [
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
            'nineteen',
        ];
        $list2 = ['', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred'];
        $list3 = [
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
            'vigintillion',
        ];
        // Ringgit
        $words = [];
        $num_length = strlen($ringgit);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $ringgit = substr('00'.$ringgit, -$max_length);
        $num_levels = str_split($ringgit, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' '.$list1[$hundreds].' hundred'.' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ($tens < 20) {
                $tens = ($tens ? ' '.$list1[$tens].' ' : '');
            } else {
                $tens = (int) ($tens / 10);
                $tens = ' '.$list2[$tens].' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' '.$list1[$singles].' ';
            }
            $words[] = $hundreds.$tens.$singles.(($levels && (int) ($num_levels[$i])) ? ' '.$list3[$levels].' ' : '');
        } // end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        $ringgit = implode(' ', $words).($currency == 'myr' ? ' ringgit ' : ' dollar ');

        // Cent
        $words = [];
        $num_length = strlen($cent);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $cent = substr('00'.$cent, -$max_length);
        $num_levels = str_split($cent, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' '.$list1[$hundreds].' hundred'.' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ($tens < 20) {
                $tens = ($tens ? ' '.$list1[$tens].' ' : '');
            } else {
                $tens = (int) ($tens / 10);
                $tens = ' '.$list2[$tens].' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' '.$list1[$singles].' ';
            }
            $words[] = $hundreds.$tens.$singles.(($levels && (int) ($num_levels[$i])) ? ' '.$list3[$levels].' ' : '');
        } // end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        $cent = implode(' ', $words);
        if ($cent != '') {
            $cent = 'and '.$cent.($currency == 'myr' ? ' sen' : ' cent');
        }
        if ($is_negative) {
            return 'negative '.$ringgit.$cent;
        }

        return $ringgit.$cent;
    }
}

if (! function_exists('is_create_link')) {
    function isCreateLink(): bool
    {
        $route_name = Route::currentRouteName();
        $names = ['customer.create_link'];

        if (in_array($route_name, $names, true)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('hasUnreadNotifications')) {
    function hasUnreadNotifications()
    {
        return Session::get('unread_noti_count') > 0;
    }
}

if (! function_exists('hasUnreadApprovals')) {
    function hasUnreadApprovals()
    {
        return Cache::get('unread_approval_count') > 0;
    }
}

if (! function_exists('getInvolvedProductChild')) {
    function getInvolvedProductChild(?int $production_id = null): array
    {
        $involved_ids = [];

        // Involved in production
        if ($production_id != null) {
            $pm_ids = ProductionMilestone::where('production_id', $production_id)->pluck('id');
            $pmm_ids = ProductionMilestoneMaterial::whereNotIn('production_milestone_id', $pm_ids)->pluck('product_child_id')->toArray();
        } else {
            $pmm_ids = ProductionMilestoneMaterial::distinct()->whereNotNull('product_child_id')->pluck('product_child_id')->toArray();
        }
        $involved_ids = array_merge($involved_ids, $pmm_ids);

        // Involved in DO
        $dopc_ids = DeliveryOrderProductChild::pluck('product_children_id')->toArray();
        $involved_ids = array_merge($involved_ids, $dopc_ids);

        // Involved in QUO/SO (Exclude converted)
        $converted_sale_ids = Sale::where('status', Sale::STATUS_CONVERTED)->pluck('id');
        $converted_sp_ids = SaleProduct::whereIn('sale_id', $converted_sale_ids)->pluck('id');

        $assigned_pc_ids = SaleProductChild::distinct()
            ->whereNotIn('sale_product_id', $converted_sp_ids)
            ->pluck('product_children_id')
            ->toArray();

        $involved_ids = array_merge($involved_ids, $assigned_pc_ids);

        // Involved in task
        $tmi_ids = TaskMilestoneInventory::where('inventory_type', ProductChild::class)->pluck('inventory_id')->toArray();
        $involved_ids = array_merge($involved_ids, $tmi_ids);

        return array_unique($involved_ids);
    }
}

if (! function_exists('generateSku')) {
    function generateSku(string $prefix, array $existing_skus = [], ?bool $is_hi_ten = null): string
    {
        $sku = null;
        $year = now()->format('y');
        $staring_num = 1;
        $digits_length = 6;
        $formatted_prefix = $prefix;
        $user_branch = getCurrentUserBranch();

        if ($user_branch !== null && $is_hi_ten !== null) {
            if (! $is_hi_ten) { // Powercool
                if ($user_branch == Branch::LOCATION_PENANG) {
                    $formatted_prefix = 'P'.$formatted_prefix;
                } elseif ($user_branch == Branch::LOCATION_KL) {
                    $formatted_prefix = 'W'.$formatted_prefix;
                }
            } elseif ($user_branch == Branch::LOCATION_PENANG) { // Hi-ten
                $formatted_prefix = 'PH'.$formatted_prefix;
            }
        }

        while (true) {
            $digits = (string) $staring_num;

            while (strlen($digits) < $digits_length) {
                $digits = '0'.$digits;
            }
            $digits = $year.'/'.$digits; // Add year

            if ($formatted_prefix == '') {
                $sku = strtoupper($digits);
            } else {
                $sku = strtoupper($formatted_prefix.'-'.$digits);
            }

            if (! in_array($sku, $existing_skus)) {
                break;
            }
            $staring_num++;
        }

        return $sku;
    }
}

if (! function_exists('getCurrentUserBranch')) {
    function getCurrentUserBranch(): ?int
    {
        if (Auth::hasUser()) {
            if (isSuperAdmin()) {
                return Session::get('as_branch');
            }

            $user_branch = Auth::user()->branch;
            if ($user_branch != null) {
                return $user_branch->location;
            }
        }

        return null;
    }
}

if (! function_exists('getCurrentUserWarehouse')) {
    function getCurrentUserWarehouse(): ?string
    {
        $user_branch = getCurrentUserBranch();
        if ($user_branch == Branch::LOCATION_KL) {
            return 'HQ';
        } elseif ($user_branch == Branch::LOCATION_PENANG) {
            return 'Penang';
        } elseif ($user_branch == Branch::LOCATION_EVERY) {
            return 'HQ';
        }

        return null;
    }
}

if (! function_exists('isHiTen')) {
    function isHiTen(int $company_group): bool
    {
        return $company_group == 2;
    }
}

if (! function_exists('getPaymentMethodCreditTermIds')) {
    function getPaymentMethodCreditTermIds()
    {
        return PaymentMethod::where('name', 'like', '%credit term%')->pluck('id')->toArray();
    }
}

if (! function_exists('getPaymentMethodOptionalDueDateIds')) {
    function getPaymentMethodOptionalDueDateIds()
    {
        return PaymentMethod::where('name', 'like', '%credit term%')
            ->orWhere('name', 'like', '%installment%')
            ->pluck('id')->toArray();
    }
}
