<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleAdhocService extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_sst' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function adhocService()
    {
        return $this->belongsTo(AdhocService::class);
    }

    public function deliveryOrderAdhocServices()
    {
        return $this->hasMany(DeliveryOrderAdhocService::class);
    }

    /**
     * Get the effective amount (override if set, otherwise original amount)
     */
    public function getEffectiveAmount(): float
    {
        return $this->override_amount ?? $this->amount;
    }

    /**
     * Get the SST amount if SST is enabled
     */
    public function getSstAmount(): float
    {
        if (!$this->is_sst) {
            return 0;
        }

        return $this->getEffectiveAmount() * ($this->sst_value ?? 0) / 100;
    }

    /**
     * Get the total amount including SST
     */
    public function getTotalAmount(): float
    {
        return $this->getEffectiveAmount() + $this->getSstAmount();
    }
}
