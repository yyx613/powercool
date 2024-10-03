<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleProduct extends Model
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

    public function remainingQty() {
        return $this->qty - DeliveryOrderProduct::where('sale_product_id', $this->id)->sum('qty');
    }

    public function discountAmount() {
        if ($this->promotion_id != null) {
            $promo = Promotion::where('id', $this->promotion_id)->first();

            if ($promo->type == 'val') {
                return $promo->amount;
            } else if ($promo->type == 'perc') {
                return ($this->qty * $this->amount) * $promo->amount / 100;
            }
        }

        return null;
    }

    public function attachedToDO(): bool {
        return DeliveryOrderProduct::where('sale_product_id', $this->id)->exists();
    }

    public function sale() {
        return $this->belongsTo(Sale::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function children() {
        return $this->hasMany(SaleProductChild::class);
    }
}
