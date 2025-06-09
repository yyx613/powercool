<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMilestoneReject extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:d M Y H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by')->withoutGlobalScope(BranchScope::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by')->withoutGlobalScope(BranchScope::class);
    }

    public function milestoneMaterials()
    {
        return $this->hasMany(ProductionMilestoneMaterial::class)->withTrashed();
    }

    public function getSubmittedAtAttribute($val)
    {
        return Carbon::parse($val)->format('d M Y, H:i');
    }
}
