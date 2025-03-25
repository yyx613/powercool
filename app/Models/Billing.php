<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([BranchScope::class])]
class Billing extends Model
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

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'billing_invoice', 'billing_id', 'invoice_id');
    }

    public function einvoice()
    {
        return $this->morphOne(EInvoice::class, 'einvoiceable');
    }

    public function saleProducts()
    {
        return $this->belongsToMany(SaleProduct::class, 'billing_sale_product', 'billing_id', 'sale_product_id')
            ->withPivot('custom_unit_price')
            ->withTimestamps();
    }
}
