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

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function defaultBillingAddress($customer_id) {
        return self::where('type', self::TYPE_BILLING)->where('customer_id', $customer_id)->first();
    }

    public function defaultDeliveryAddress($customer_id) {
        return self::where('type', self::TYPE_DELIVERY)->where('customer_id', $customer_id)->first();
    }
}
