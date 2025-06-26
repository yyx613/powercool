<?php

namespace App\Models;

use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[ScopedBy([BranchScope::class, ApprovedScope::class])]
class DeliveryOrder extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_VOIDED = 1;
    const STATUS_CONVERTED = 2;
    const STATUS_APPROVAL_PENDING = 4;
    const STATUS_APPROVAL_APPROVED = 5;
    const STATUS_APPROVAL_REJECTED = 6;
    const TRANSPORT_ACK_TYPE_DELIVERY = 1;
    const TRANSPORT_ACK_TYPE_COLLECTION = 2;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function products()
    {
        return $this->hasMany(DeliveryOrderProduct::class);
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function approval()
    {
        return $this->morphOne(Approval::class, 'object');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScope(BranchScope::class);
    }

    public function generateSku(): string
    {
        $sku = null;

        while (true) {
            $sku = 'DO' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::withoutGlobalScope(BranchScope::class)->where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (! $exists) {
                break;
            }
        }

        return $sku;
    }
}
