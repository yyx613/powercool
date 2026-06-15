<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([BranchScope::class])]
class SaleEnquiry extends Model
{
    use HasFactory, SoftDeletes;

    // Enquiry Source
    const SOURCE_WEBSITE = 1;
    const SOURCE_FACEBOOK = 2;
    const SOURCE_SHOPEE = 3;
    const SOURCE_LAZADA = 4;
    const SOURCE_WALK_IN = 5;
    const SOURCE_REFERRAL = 6;
    const SOURCE_INSTAGRAM = 7;
    const SOURCE_TIKTOK = 8;
    const SOURCE_XHS = 9;
    const SOURCE_PHONE = 10;
    const SOURCE_WHATSAPP = 11;
    const SOURCE_GOOGLE = 12;

    // Status
    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_CLOSED_CONVERTED = 3;
    const STATUS_CLOSED_DROPPED = 4;

    // Preferred Contact Method
    const CONTACT_WHATSAPP = 1;
    const CONTACT_CALL = 2;
    const CONTACT_EMAIL = 3;

    // Priority Level
    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;

    // Quality - Response Tracking
    const QUALITY_SEEN_AND_REPLY = 1;
    const QUALITY_SEEN_NO_REPLY = 2;
    const QUALITY_NO_SEEN_NO_REPLY = 3;

    // Type of Enquiry
    const TYPE_PRODUCT_PRICING = 1;
    const TYPE_SERVICE = 2;
    const TYPE_RELOCATION_FRIDGE = 3;
    const TYPE_TRADE_IN = 4;
    const TYPE_RENTAL = 5;

    protected $guarded = [];

    protected $casts = [
        'enquiry_date' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    // Relationships
    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }
    
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function acceptedByUser()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * The assigned salesperson must accept or reject an enquiry before they may
     * view its details. Anyone else (e.g. the manager/creator) is never gated.
     */
    public function isPendingActionBy($userId): bool
    {
        return (int) $this->assigned_user_id === (int) $userId
            && $this->accepted_at === null
            && $this->rejected_at === null;
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'sale_enquiry_id');
    }

    /**
     * Delivery orders spawned from this enquiry's sale orders. Linked indirectly
     * through delivery_order_products.sale_order_id.
     */
    public function relatedDeliveryOrders()
    {
        $saleOrderIds = $this->sales()->where('type', Sale::TYPE_SO)->pluck('id');

        if ($saleOrderIds->isEmpty()) {
            return DeliveryOrder::whereRaw('1 = 0');
        }

        $deliveryOrderIds = DeliveryOrderProduct::whereIn('sale_order_id', $saleOrderIds)
            ->pluck('delivery_order_id')
            ->unique();

        return DeliveryOrder::whereIn('id', $deliveryOrderIds);
    }

    /**
     * Invoices spawned from this enquiry's delivery orders.
     */
    public function relatedInvoices()
    {
        $invoiceIds = $this->relatedDeliveryOrders()->whereNotNull('invoice_id')->pluck('invoice_id')->unique();

        if ($invoiceIds->isEmpty()) {
            return Invoice::whereRaw('1 = 0');
        }

        return Invoice::whereIn('id', $invoiceIds);
    }

    /**
     * Auto-derived progress timeline of the records this enquiry has spawned.
     * Each milestone is [key, label, ref, date, done]; values are derived live
     * from the linked Customer / Quotation / Sale Order / Delivery Order /
     * Invoice records rather than stored on the enquiry.
     *
     * @return array<int, array{key:string,label:string,ref:?string,date:?\Carbon\Carbon,done:bool}>
     */
    public function progress(): array
    {
        // Debtor (customer) — earliest related sale that carries a customer.
        $debtorSale = $this->sales()
            ->whereNotNull('customer_id')
            ->with('customer')
            ->orderBy('id')
            ->first();
        $debtor = $debtorSale?->customer;

        $quotation = $this->sales()->where('type', Sale::TYPE_QUO)->orderBy('id')->first();
        $saleOrder = $this->sales()->where('type', Sale::TYPE_SO)->orderBy('id')->first();
        $deliveryOrder = $this->relatedDeliveryOrders()->orderBy('id')->first();
        $invoice = $this->relatedInvoices()->orderBy('id')->first();

        return [
            [
                'key' => 'new',
                'label' => __('New'),
                'ref' => $this->sku,
                'date' => $this->enquiry_date,
                'done' => true,
            ],
            [
                'key' => 'in_progress',
                'label' => __('In Progress'),
                'ref' => null,
                'date' => $this->accepted_at,
                'done' => $this->accepted_at !== null || $this->status >= self::STATUS_IN_PROGRESS,
            ],
            [
                'key' => 'debtor',
                'label' => __('Create Debtor'),
                'ref' => $debtor?->sku,
                'date' => $debtor?->created_at,
                'done' => $debtor !== null,
            ],
            [
                'key' => 'quotation',
                'label' => __('Create Quotation No'),
                'ref' => $quotation?->sku,
                'date' => $quotation?->custom_date ?? $quotation?->created_at,
                'done' => $quotation !== null,
            ],
            [
                'key' => 'sale_order',
                'label' => __('Create SO No'),
                'ref' => $saleOrder?->sku,
                'date' => $saleOrder?->custom_date ?? $saleOrder?->created_at,
                'done' => $saleOrder !== null,
            ],
            [
                'key' => 'delivery_order',
                'label' => __('Create DO No'),
                'ref' => $deliveryOrder?->sku,
                'date' => $deliveryOrder?->created_at,
                'done' => $deliveryOrder !== null,
            ],
            [
                'key' => 'invoice',
                'label' => __('Create INV No'),
                'ref' => $invoice?->sku,
                'date' => $invoice?->created_at,
                'done' => $invoice !== null,
            ],
        ];
    }

    /**
     * Whether this enquiry currently has a pending "No Deal" approval awaiting
     * management action.
     */
    public function hasPendingNoDealApproval(): bool
    {
        return Approval::withoutGlobalScope(BranchScope::class)
            ->where('object_type', self::class)
            ->where('object_id', $this->id)
            ->where('status', Approval::STATUS_PENDING_APPROVAL)
            ->where('data', 'like', '%is_no_deal%')
            ->exists();
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScope(BranchScope::class);
    }

    public function countryModel()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function stateModel()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
