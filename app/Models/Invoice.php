<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[ScopedBy([BranchScope::class])]
class Invoice extends Model
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

    public function deliveryOrders() {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function branch() {
        return $this->morphOne(Branch::class, 'object');
    }

    public function consolidatedEInvoices()
    {
        return $this->belongsToMany(ConsolidatedEInvoice::class, 'consolidated_e_invoice_invoice');
    }

    public function billings()
    {
        return $this->belongsToMany(Billing::class, 'billing_invoice', 'invoice_id', 'billing_id');
    }

    public function einvoice()
    {
        return $this->morphOne(EInvoice::class, 'einvoiceable');
    }

    public function generateSku(): string {
        $sku = null;
        
        while (true) {
            $sku = 'INV' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::withoutGlobalScope(BranchScope::class)->where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (!$exists) {
                break;
            }
        }

        return $sku;
    }
}
