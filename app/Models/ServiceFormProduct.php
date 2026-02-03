<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceFormProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_foc' => 'boolean',
        'with_sst' => 'boolean',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'sst_amount' => 'decimal:2',
        'sst_value' => 'decimal:2',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function serviceForm()
    {
        return $this->belongsTo(ServiceForm::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope(\App\Models\Scopes\BranchScope::class);
    }

    /**
     * Calculate line total (qty * unit_price - discount)
     */
    public function lineTotal(): float
    {
        if ($this->is_foc) {
            return 0;
        }

        $total = $this->qty * $this->unit_price;
        $total -= $this->discount ?? 0;

        return max(0, $total);
    }

    /**
     * Calculate SST amount for this line item
     */
    public function calculateSstAmount(?float $sstPercentage = null): float
    {
        if (! $this->with_sst || $this->is_foc) {
            return 0;
        }

        $percentage = $sstPercentage ?? $this->sst_value ?? 0;

        return $this->lineTotal() * ($percentage / 100);
    }

    /**
     * Get description (product name or custom description)
     */
    public function getDescription(): string
    {
        if ($this->product) {
            return $this->product->model_desc ?? $this->product->sku ?? '';
        }

        return $this->custom_desc ?? '';
    }

    /**
     * Get item code (product SKU or empty)
     */
    public function getItemCode(): string
    {
        return $this->product?->sku ?? '';
    }

    /**
     * Get warranty periods for this line item
     */
    public function warrantyPeriods()
    {
        return $this->hasMany(ServiceFormProductWarrantyPeriod::class);
    }
}
