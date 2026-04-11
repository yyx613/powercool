<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];

        $currencies = [
            ['name' => 'AFN', 'country' => 'Afghanistan', 'currency_name' => 'Afghan Afghani', 'code' => 'AFN', 'symbol' => '؋'],
            ['name' => 'AMD', 'country' => 'Armenia', 'currency_name' => 'Armenian Dram', 'code' => 'AMD', 'symbol' => '֏, դր'],
            ['name' => 'AZN', 'country' => 'Azerbaijan', 'currency_name' => 'Azerbaijani Manat', 'code' => 'AZN', 'symbol' => '₼'],
            ['name' => 'BHD', 'country' => 'Bahrain', 'currency_name' => 'Bahraini Dinar', 'code' => 'BHD', 'symbol' => '.د.ب'],
            ['name' => 'BDT', 'country' => 'Bangladesh', 'currency_name' => 'Bangladeshi Taka', 'code' => 'BDT', 'symbol' => '৳'],
            ['name' => 'BTN', 'country' => 'Bhutan', 'currency_name' => 'Bhutanese Ngultrum', 'code' => 'BTN', 'symbol' => 'Nu'],
            ['name' => 'BND', 'country' => 'Brunei', 'currency_name' => 'Brunei Dollar', 'code' => 'BND', 'symbol' => 'B$'],
            ['name' => 'KHR', 'country' => 'Cambodia', 'currency_name' => 'Cambodian Riel', 'code' => 'KHR', 'symbol' => '៛'],
            ['name' => 'CNY', 'country' => 'China', 'currency_name' => 'Chinese Yuan Renminbi', 'code' => 'CNY', 'symbol' => '¥'],
            ['name' => 'HKD', 'country' => 'Hong Kong', 'currency_name' => 'Hong Kong Dollar', 'code' => 'HKD', 'symbol' => 'HK$'],
            ['name' => 'MOP', 'country' => 'Macau', 'currency_name' => 'Macanese Pataca', 'code' => 'MOP', 'symbol' => 'MOP$'],
            ['name' => 'EUR', 'country' => 'Cyprus', 'currency_name' => 'European Euro', 'code' => 'EUR', 'symbol' => '€'],
            ['name' => 'GEL', 'country' => 'Georgia', 'currency_name' => 'Georgian Lari', 'code' => 'GEL', 'symbol' => 'ლარი'],
            ['name' => 'INR', 'country' => 'India', 'currency_name' => 'Indian Rupee', 'code' => 'INR', 'symbol' => '₹'],
            ['name' => 'IDR', 'country' => 'Indonesia', 'currency_name' => 'Indonesian Rupiah', 'code' => 'IDR', 'symbol' => 'Rp'],
            ['name' => 'IRR', 'country' => 'Iran', 'currency_name' => 'Iranian Rial', 'code' => 'IRR', 'symbol' => '﷼'],
            ['name' => 'IQD', 'country' => 'Iraq', 'currency_name' => 'Iraqi Dinar', 'code' => 'IQD', 'symbol' => 'ع.د'],
            ['name' => 'ILS', 'country' => 'Israel', 'currency_name' => 'Israeli New Shekel', 'code' => 'ILS', 'symbol' => '₪'],
            ['name' => 'JPY', 'country' => 'Japan', 'currency_name' => 'Japanese Yen', 'code' => 'JPY', 'symbol' => '¥'],
            ['name' => 'JOD', 'country' => 'Jordan', 'currency_name' => 'Jordanian Dinar', 'code' => 'JOD', 'symbol' => 'ينار'],
            ['name' => 'KZT', 'country' => 'Kazakhstan', 'currency_name' => 'Kazakhstani Tenge', 'code' => 'KZT', 'symbol' => 'лв'],
            ['name' => 'KWD', 'country' => 'Kuwait', 'currency_name' => 'Kuwaiti Dinar', 'code' => 'KWD', 'symbol' => 'ك'],
            ['name' => 'KGS', 'country' => 'Kyrgyzstan', 'currency_name' => 'Kyrgyzstani Som', 'code' => 'KGS', 'symbol' => 'som'],
            ['name' => 'LAK', 'country' => 'Laos', 'currency_name' => 'Lao Kip', 'code' => 'LAK', 'symbol' => '₭'],
            ['name' => 'LBP', 'country' => 'Lebanon', 'currency_name' => 'Lebanese Pound', 'code' => 'LBP', 'symbol' => '£L, ل.ل'],
            ['name' => 'MYR', 'country' => 'Malaysia', 'currency_name' => 'Malaysian Ringgit', 'code' => 'MYR', 'symbol' => 'RM'],
            ['name' => 'MVR', 'country' => 'Maldives', 'currency_name' => 'Maldivian Rufiyaa', 'code' => 'MVR', 'symbol' => 'MRf'],
            ['name' => 'MNT', 'country' => 'Mongolia', 'currency_name' => 'Mongolian Tögrög', 'code' => 'MNT', 'symbol' => '₮'],
            ['name' => 'MMK', 'country' => 'Myanmar', 'currency_name' => 'Myanma Kyat', 'code' => 'MMK', 'symbol' => 'K'],
            ['name' => 'NPR', 'country' => 'Nepal', 'currency_name' => 'Nepalese Rupee', 'code' => 'NPR', 'symbol' => 'Rs'],
            ['name' => 'KPW', 'country' => 'North Korea', 'currency_name' => 'North Korean Won', 'code' => 'KPW', 'symbol' => '₩'],
            ['name' => 'OMR', 'country' => 'Oman', 'currency_name' => 'Omani Rial', 'code' => 'OMR', 'symbol' => 'ر.ع'],
            ['name' => 'PKR', 'country' => 'Pakistan', 'currency_name' => 'Pakistani Rupee', 'code' => 'PKR', 'symbol' => 'Rs'],
            ['name' => 'PHP', 'country' => 'Philippines', 'currency_name' => 'Philippine Peso', 'code' => 'PHP', 'symbol' => '₱'],
            ['name' => 'QAR', 'country' => 'Qatar', 'currency_name' => 'Qatari Riyal', 'code' => 'QAR', 'symbol' => 'ر.ق'],
            ['name' => 'SAR', 'country' => 'Saudi Arabia', 'currency_name' => 'Saudi Riyal', 'code' => 'SAR', 'symbol' => null],
            ['name' => 'SGD', 'country' => 'Singapore', 'currency_name' => 'Singapore Dollar', 'code' => 'SGD', 'symbol' => '$'],
            ['name' => 'KRW', 'country' => 'South Korea', 'currency_name' => 'South Korean Won', 'code' => 'KRW', 'symbol' => '₩'],
            ['name' => 'LKR', 'country' => 'Sri Lanka', 'currency_name' => 'Sri Lankan Rupee', 'code' => 'LKR', 'symbol' => 'Rs'],
            ['name' => 'SYP', 'country' => 'Syria', 'currency_name' => 'Syrian Pound', 'code' => 'SYP', 'symbol' => '£S'],
            ['name' => 'TWD', 'country' => 'Taiwan', 'currency_name' => 'New Taiwan Dollar', 'code' => 'TWD', 'symbol' => 'NT$'],
            ['name' => 'TJS', 'country' => 'Tajikistan', 'currency_name' => 'Tajikistani Somoni', 'code' => 'TJS', 'symbol' => 'TJS'],
            ['name' => 'THB', 'country' => 'Thailand', 'currency_name' => 'Thai Baht', 'code' => 'THB', 'symbol' => '฿'],
            ['name' => 'USD', 'country' => 'Timor-Leste', 'currency_name' => 'United States Dollar', 'code' => 'USD', 'symbol' => 'US$'],
            ['name' => 'TRY', 'country' => 'Turkey', 'currency_name' => 'Turkish Lira', 'code' => 'TRY', 'symbol' => null],
            ['name' => 'TMT', 'country' => 'Turkmenistan', 'currency_name' => 'Turkmenistan Manat', 'code' => 'TMT', 'symbol' => 'm'],
            ['name' => 'AED', 'country' => 'United Arab Emirates', 'currency_name' => 'UAE Dirham', 'code' => 'AED', 'symbol' => 'AED'],
            ['name' => 'UZS', 'country' => 'Uzbekistan', 'currency_name' => 'Uzbekistani Som', 'code' => 'UZS', 'symbol' => 'sum'],
            ['name' => 'VND', 'country' => 'Vietnam', 'currency_name' => 'Vietnamese Dong', 'code' => 'VND', 'symbol' => '₫'],
        ];

        foreach ($currencies as $currencyData) {
            foreach ($branches as $branch) {
                $cu = Currency::updateOrCreate(
                    ['name' => $currencyData['name']],
                    [
                        'country' => $currencyData['country'],
                        'currency_name' => $currencyData['currency_name'],
                        'code' => $currencyData['code'],
                        'symbol' => $currencyData['symbol'],
                        'is_active' => true,
                    ]
                );

                $existingBranch = Branch::where('object_type', Currency::class)
                    ->where('object_id', $cu->id)
                    ->where('location', $branch)
                    ->first();

                if (!$existingBranch) {
                    Branch::create([
                        'object_type' => Currency::class,
                        'object_id' => $cu->id,
                        'location' => $branch,
                    ]);
                }
            }
        }
    }
}
