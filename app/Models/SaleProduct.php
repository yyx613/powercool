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

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    /**
     * remaining qty to convert to DO
     */
    public function remainingQty()
    {
        $children_count = count($this->children);

        $dops = DeliveryOrderProduct::where('sale_order_id', $this->sale->id)
            ->where('sale_product_id', $this->id)
            ->get();

        $do_children_count = 0;
        for ($i = 0; $i < count($dops); $i++) {
            $do_children_count += count($dops[$i]->children);
        }

        return $children_count - $do_children_count;
    }

    /**
     * remaining qty for raw material
     */
    public function remainingQtyForRM()
    {
        return $this->qty - DeliveryOrderProduct::where('sale_product_id', $this->id)->sum('qty');
    }

    public function discountAmount()
    {
        $amount = 0;
        $price = $this->qty * ($this->override_selling_price ?? $this->unit_price);

        if ($this->promotion_id != null) {
            $promo = Promotion::where('id', $this->promotion_id)->first();

            if ($promo->type == 'val') {
                $amount += $promo->amount;
            } elseif ($promo->type == 'perc') {
                $amount += $price * $promo->amount / 100;
            }
        }

        $amount += ($this->discount ?? 0);

        return $amount;
    }

    public function promotionAmount()
    {
        $amount = 0;
        $price = $this->qty * ($this->override_selling_price ?? $this->unit_price);

        if ($this->promotion_id != null) {
            $promo = Promotion::where('id', $this->promotion_id)->first();

            if ($promo->type == 'val') {
                $amount += $promo->amount;
            } elseif ($promo->type == 'perc') {
                $amount += $price * $promo->amount / 100;
            }
        }

        return $amount;
    }

    public function attachedToDO(): bool
    {
        return DeliveryOrderProduct::where('sale_product_id', $this->id)->exists();
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function children()
    {
        return $this->hasMany(SaleProductChild::class);
    }

    public function warrantyPeriod()
    {
        return $this->belongsTo(WarrantyPeriod::class);
    }

    public function billings()
    {
        return $this->belongsToMany(Billing::class, 'billing_sale_product', 'sale_product_id', 'billing_id')
            ->withPivot('custom_unit_price')
            ->withTimestamps();
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
}
