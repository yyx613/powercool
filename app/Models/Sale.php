<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[ScopedBy([BranchScope::class])]
class Sale extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_QUO = 1; // QUOTATION id
    const TYPE_SO = 2; // SALE ORDER id
    const TYPE_PENDING = 3; // PENDING ASSIGN SALE PERSON

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CONVERTED = 2;
    const STATUS_CANCELLED = 3;

    const PAYMENT_STATUS_UNPAID = 1;
    const PAYMENT_STATUS_PARTIALLY_PAID = 2;
    const PAYMENT_STATUS_PAID = 3;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function products() {
        return $this->hasMany(SaleProduct::class, 'sale_id');
    }

    public function saleperson() {
        return $this->belongsTo(User::class, 'sale_id');
    }
    
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function platform(){
        return $this->belongsTo(Platform::class, 'platform_id');
    }

    public function branch() {
        return $this->morphOne(Branch::class, 'object');
    }

    public function getReferenceAttribute($val) {
        if ($this->type == self::TYPE_QUO) {
            return $val;
        } else if ($this->type == self::TYPE_SO) {
            return $val == null ? null : join(',', json_decode($val, true));
        }
    }

    public function getRemarkAttribute($val) {
        if ($this->type == self::TYPE_QUO) {
            return $val;
        } else if ($this->type == self::TYPE_SO) {
            return $val == null ? null : join(',', json_decode($val, true));
        }
    }

    public function paymentMethodHumanRead(): string {
        switch ($this->payment_method) {
            case 'cash':
                return 'Cash';
            case 'term':
                return 'Term';
            case 'banking':
                return 'Banking';
            case 'tng':
                return 'T&G';
            case 'cheque':
                return 'Cheque';
            default:
                return $this->payment_method ?? '-';
        }
    }

    public function hasNoMoreQtyToConvertDO(): bool {
        $fully_converted = true;
                
        $sps = $this->products;
        for ($j=0; $j < count($sps); $j++) {
            if ($sps[$j]->remainingQty() > 0) {
                $fully_converted = false;
                break;
            }
        }

        return $fully_converted;
    }

    public function getFormattedPaymentAmount(): ?array {
        if ($this->payment_amount == null) {
            return null;
        } else if (str_contains($this->payment_amount, ',')) {
            return array_map(function($value) {
                return number_format($value, 2);
            }, array_reverse(explode(',', $this->payment_amount)));
        } else {
            return [$this->payment_amount];
        }
    }
}
