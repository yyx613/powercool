<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([BranchScope::class])]
class TransportAcknowledgement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'generated_by')->withoutGlobalScope(BranchScope::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function products()
    {
        return $this->hasMany(TransportAcknowledgementProduct::class, 'transport_acknowledgement_id');
    }

    public function typeName(): ?string
    {
        if ($this->type == DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY) {
            return 'Delivery';
        } else if ($this->type == DeliveryOrder::TRANSPORT_ACK_TYPE_COLLECTION) {
            return 'Collection';
        } else if (str_starts_with((string) $this->sku, 'DL')) {
            return 'Delivery';
        } else if (str_starts_with((string) $this->sku, 'CL')) {
            return 'Collection';
        } else {
            return null;
        }
    }

    public function dealerName(): ?string
    {
        if ($this->dealer_id == '-1' || $this->dealer_id == '-2') {
            return self::dealerLabel($this->dealer_id, null, null);
        }

        $dealer = $this->belongsTo(Dealer::class, 'dealer_id')->first(['name', 'company_group']);

        if ($dealer == null) {
            return null;
        }

        return self::dealerLabel($this->dealer_id, $dealer->name, $dealer->company_group);
    }

    /**
     * Resolve the dealer display label for a transport acknowledgement.
     *
     * The dealer_id may be one of the hardcoded company groups (-1 = Powercool,
     * -2 = Hi-Ten) or a real dealer record, in which case the label is the
     * dealer's name suffixed with its company group, e.g. "Ahmad (Hi-Ten)".
     */
    public static function dealerLabel($dealer_id, ?string $name, ?int $company_group): string
    {
        if ((string) $dealer_id === '-1') {
            return 'Powercool';
        }

        if ((string) $dealer_id === '-2') {
            return 'Hi-Ten';
        }

        $group = isHiTen((int) $company_group) ? 'Hi-Ten' : 'Powercool';

        return trim((string) $name).' ('.$group.')';
    }
}
