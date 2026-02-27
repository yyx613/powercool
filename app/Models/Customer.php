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
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_PENDING_FILL_UP_INFO = 2;
    const STATUS_APPROVAL_PENDING = 3;
    const STATUS_APPROVAL_REJECTED = 4;
    const STATUS_APPROVAL_APPROVED = 5;

    const BUSINESS_TYPES = [
        1 => 'Business',
        2 => 'Individual',
        3 => 'Government',
    ];

    protected $guarded = [];

    protected $casts = [
        'under_warranty' => 'boolean',
        'mobile_number' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function pictures()
    {
        return $this->morphMany(Attachment::class, 'object')->orderBy('id', 'desc');
    }

    public function locations()
    {
        return $this->hasMany(CustomerLocation::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function creditTerms()
    {
        return $this->morphMany(ObjectCreditTerm::class, 'object')->orderBy('id', 'desc');
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryOrder::class, 'customer_id');
    }

    public function msicCode()
    {
        return $this->belongsTo(MsicCode::class, 'msic_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function debtorType()
    {
        return $this->belongsTo(DebtorType::class, 'debtor_type_id');
    }

    public function salesAgents()
    {
        return $this->hasMany(CustomerSaleAgent::class);
    }

    public function generateSku(string $company_first_alphabet): string
    {
        $sku = null;
        $staring_num = 1;

        while (true) {
            $digits = (string) $staring_num;

            while (strlen($digits) < 3) { // Make 3 digits
                $digits = '0' . $digits;
            }
            $sku = strtoupper('300-' . $company_first_alphabet . $digits);

            $exists = self::withoutGlobalScope(BranchScope::class)->where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (! $exists) {
                break;
            }
            $staring_num++;
        }

        return $sku;
    }

    public static function forEinvoiceFilled(int $id): bool
    {
        $cus = self::where('id', $id)->first();
        if ($cus != null && $cus->for_einvoice == true) {
            if (
                $cus->type == null || $cus->tin_number == null || $cus->company_registration_number == null ||
                $cus->msic_id == null || $cus->registered_name == null || $cus->phone == null ||
                $cus->email == null
            ) {
                return false;
            }
        }
        return true;
    }

    public function statusToLabel(int $status): string
    {
        switch ($status) {
            case self::STATUS_INACTIVE:
                return 'Inactive';
            case self::STATUS_ACTIVE:
                return 'Active';
            case self::STATUS_PENDING_FILL_UP_INFO:
                return 'Pending Fill Up Info';
            case self::STATUS_APPROVAL_PENDING:
                return 'Pending Approval';
            case self::STATUS_APPROVAL_APPROVED:
                return 'Approved';
            case self::STATUS_APPROVAL_REJECTED:
                return 'Rejected';
            default:
                return '';
        }
    }
}
