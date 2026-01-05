<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrderProductAccessory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function doProduct()
    {
        return $this->belongsTo(DeliveryOrderProduct::class, 'delivery_order_product_id');
    }

    public function saleProductAccessory()
    {
        return $this->belongsTo(SaleProductAccessory::class, 'sale_product_accessory_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'accessory_id');
    }
}
