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
    const SOURCE_WHATSAPP = 2;
    const SOURCE_PHONE = 3;
    const SOURCE_WALK_IN = 4;
    const SOURCE_SOCIAL_MEDIA = 5;
    const SOURCE_REFERRAL = 6;

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

    protected $guarded = [];

    protected $casts = [
        'enquiry_date' => 'datetime',
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
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'sale_enquiry_id');
    }
}
