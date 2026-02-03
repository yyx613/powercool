<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([BranchScope::class])]
class ServiceForm extends Model
{
    use HasFactory, SoftDeletes;

    const WARRANTY_UNDER = 1;

    const WARRANTY_OUT = 2;

    const CHECKLIST_ITEMS = [
        'compressor_accessories' => 'Check Compressor / Accessories',
        'motor_fan' => 'Check F Motor / Blower Fan / V.Fan',
        'gas_volume' => 'Check Gas Volume (psi)',
        'digital' => 'Check Digital',
        'cooling_coil' => 'Check Cooling Coil',
        'plug' => 'Check Plug',
        'gasket' => 'Check Gasket',
        'drain' => 'Check Drain',
    ];

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'invoice_date' => 'date',
        'date_to_attend' => 'date',
        'report_checklist' => 'array',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withoutGlobalScope(BranchScope::class);
    }

    public function customerLocation()
    {
        return $this->belongsTo(CustomerLocation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope(BranchScope::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class)->withoutGlobalScope(BranchScope::class);
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class)->withoutGlobalScope(BranchScope::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id')->withoutGlobalScope(BranchScope::class);
    }

    public function products()
    {
        return $this->hasMany(ServiceFormProduct::class)->orderBy('sequence');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class)->withoutGlobalScope(BranchScope::class);
    }

    public function warrantyPeriod()
    {
        return $this->belongsTo(WarrantyPeriod::class)->withoutGlobalScope(BranchScope::class);
    }

    /**
     * Calculate totals from products
     */
    public function calculateTotals(?float $sstPercentage = null): array
    {
        $subtotal = 0;
        $totalTax = 0;

        foreach ($this->products as $product) {
            $lineTotal = $product->lineTotal();
            $subtotal += $lineTotal;

            if ($product->with_sst && ! $product->is_foc) {
                $totalTax += $product->calculateSstAmount($sstPercentage);
            }
        }

        return [
            'subtotal' => $subtotal,
            'total_tax' => $totalTax,
            'grand_total' => $subtotal + $totalTax,
        ];
    }

    public function generateSku(?bool $is_hi_ten = null): string
    {
        $existing_skus = self::withoutGlobalScope(BranchScope::class)->pluck('sku')->toArray();

        return generateSku('SF', $existing_skus, $is_hi_ten);
    }

    public static function getWarrantyStatuses(): array
    {
        return [
            self::WARRANTY_UNDER => 'Under Warranty',
            self::WARRANTY_OUT => 'Out of Warranty',
        ];
    }
}
