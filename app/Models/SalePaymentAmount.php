<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalePaymentAmount extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 1;
    const STATUS_PENDING_EDIT = 2;
    const STATUS_PENDING_DELETE = 3;

    const TYPE_IN = 1;
    const TYPE_OUT = 2;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(CreditTerm::class, 'payment_term');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function approval()
    {
        return $this->morphOne(Approval::class, 'object')->latestOfMany();
    }

    public function hasPendingApproval(): bool
    {
        return in_array($this->approval_status, [
            self::STATUS_PENDING_EDIT,
            self::STATUS_PENDING_DELETE
        ]);
    }
}
