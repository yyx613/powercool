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

    public function dealerName(): ?string
    {
        if ($this->dealer_id == '-1') {
            return 'Power Cool';
        } else if ($this->dealer_id == '-2') {
            return 'Hi Ten Trading';
        } else {
            return $this->belongsTo(Dealer::class, 'dealer_id')->value('name');
        }
    }
}
