<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryOrderProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function saleProduct() {
        return $this->belongsTo(SaleProduct::class);
    }

    public function do() {
        return $this->belongsTo(DeliveryOrder::class, 'delivery_order_id');
    }

    public function children() {
        return $this->hasMany(DeliveryOrderProductChild::class);
    }
}
