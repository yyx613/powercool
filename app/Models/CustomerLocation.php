<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLocation extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_BILLING = 1;
    const TYPE_DELIVERY = 2;
    const TYPE_BILLING_ADN_DELIVERY = 3;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function defaultBillingAddress($customer_id)
    {
        return self::whereIn('type', [self::TYPE_BILLING, self::TYPE_BILLING_ADN_DELIVERY])->where('customer_id', $customer_id)->where('is_default', true)->first();
    }

    public function defaultDeliveryAddress($customer_id)
    {
        return self::whereIn('type', [self::TYPE_DELIVERY, self::TYPE_BILLING_ADN_DELIVERY])->where('customer_id', $customer_id)->where('is_default', true)->first();
    }

    public function countrySubentityCode()
    {
        $stateCodes = [
            'Johor' => '01',
            'Kedah' => '02',
            'Kelantan' => '03',
            'Melaka' => '04',
            'Negeri Sembilan' => '05',
            'Pahang' => '06',
            'Pulau Pinang' => '07',
            'Perak' => '08',
            'Perlis' => '09',
            'Selangor' => '10',
            'Terengganu' => '11',
            'Sabah' => '12',
            'Sarawak' => '13',
            'Wilayah Persekutuan Kuala Lumpur' => '14',
            'Wilayah Persekutuan Labuan' => '15',
            'Wilayah Persekutuan Putrajaya' => '16',
            'Not Applicable' => '17',
        ];

        return $stateCodes[$this->state] ?? '17';
    }

    public function formatAddress()
    {
        $addr = null;

        if ($this->address1 != null) {
            $addr .= $this->address1.'<br>';
        }
        if ($this->address2 != null) {
            $addr .= $this->address2.'<br>';
        }
        if ($this->address3 != null) {
            $addr .= $this->address3.'<br>';
        }
        if ($this->address4 != null) {
            $addr .= $this->address4.'<br>';
        }

        return $addr;
    }
}
