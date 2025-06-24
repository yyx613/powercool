<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    const STATUS_APPROVAL_PENDING = 4;
    const STATUS_APPROVAL_APPROVED = 5;
    const STATUS_APPROVAL_REJECTED = 6;
    const PAYMENT_STATUS_UNPAID = 1;
    const PAYMENT_STATUS_PARTIALLY_PAID = 2;
    const PAYMENT_STATUS_PAID = 3;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function paymentAmounts()
    {
        return $this->hasMany(SalePaymentAmount::class);
    }

    public function products()
    {
        return $this->hasMany(SaleProduct::class, 'sale_id');
    }

    public function saleperson()
    {
        return $this->belongsTo(User::class, 'sale_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function approval()
    {
        return $this->morphOne(Approval::class, 'object');
    }

    public function getReferenceAttribute($val)
    {
        if ($this->type == self::TYPE_QUO) {
            return $val;
        } elseif ($this->type == self::TYPE_SO) {
            return $val == null ? null : implode(',', json_decode($val, true));
        }
    }

    public function getRemarkAttribute($val)
    {
        if ($this->type == self::TYPE_QUO) {
            return $val;
        } elseif ($this->type == self::TYPE_SO) {
            return $val == null ? null : implode(',', json_decode($val, true));
        }
    }

    public function paymentMethodHumanRead(): string
    {
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

    public function hasNoMoreQtyToConvertDO(): bool
    {
        $fully_converted = true;

        $sps = $this->products;
        for ($j = 0; $j < count($sps); $j++) {
            if ($sps[$j]->product->isRawMaterial() && $sps[$j]->remainingQtyForRM() > 0) {
                $fully_converted = false;
                break;
            } elseif (! $sps[$j]->product->isRawMaterial() && $sps[$j]->remainingQty() > 0) {
                $fully_converted = false;
                break;
            }
        }

        return $fully_converted;
    }

    public function getTotalAmount(): float
    {
        $prods = $this->products()->withTrashed()->get();

        $total = 0;
        for ($i = 0; $i < count($prods); $i++) {
            $total += ($prods[$i]->qty * $prods[$i]->unit_price);
        }

        return $total;
    }

    public function getPaidAmount(): float
    {
        return $this->paymentAmounts->sum('amount');
    }

    public function getTransferredTo(): ?array
    {
        if ($this->convert_to == null) {
            return null;
        }
        if (str_contains($this->convert_to, ',')) {
            return DeliveryOrder::whereIn('id', explode(',', $this->convert_to))->pluck('sku')->toArray();
        }

        return DeliveryOrder::where('id', $this->convert_to)->pluck('sku')->toArray();
    }

    public function paymentTerm()
    {
        return $this->belongsTo(CreditTerm::class, 'payment_term');
    }

    public function convertFromQuo(): bool
    {
        return self::where('convert_to', $this->id)->exists();
    }
}
