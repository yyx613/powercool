<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[ScopedBy([BranchScope::class])]
class Production extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_TO_DO = 1;

    const STATUS_DOING = 2;

    const STATUS_COMPLETED = 3;

    const STATUS_TRANSFERRED = 4;

    const STATUS_MODIFIED = 5;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_production', 'production_id', 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productChild()
    {
        return $this->belongsTo(ProductChild::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function oldProduction()
    {
        return $this->belongsTo(Production::class, 'old_production');
    }

    public function milestones()
    {
        return $this->belongsToMany(Milestone::class, 'production_milestone', 'production_id', 'milestone_id')
            ->withPivot('id', 'submitted_at')
            ->using(ProductionMilestone::class);
    }

    public function branch()
    {
        return $this->morphOne(Branch::class, 'object');
    }

    public function generateSku(): string
    {
        $sku = null;
        $staring_num = 1;
        $digits_length = 6;
        $formatted_prefix = 'PO';
        $user_branch = getCurrentUserBranch();
        $existing_skus = self::withoutGlobalScope(BranchScope::class)->pluck('sku')->toArray();

        if ($user_branch != null) {
            if ($user_branch == Branch::LOCATION_PENANG) {
                $formatted_prefix = 'P' . $formatted_prefix;
            } elseif ($user_branch == Branch::LOCATION_KL) {
                $formatted_prefix = 'W' . $formatted_prefix;
            }
        }

        while (true) {
            $digits = (string) $staring_num;

            while (strlen($digits) < $digits_length) {
                $digits = '0' . $digits;
            }
            if ($formatted_prefix == '') {
                $sku = strtoupper($digits);
            } else {
                $sku = strtoupper($formatted_prefix . '-' . $digits);
            }

            if (! in_array($sku, $existing_skus)) {
                break;
            }
            $staring_num++;
        }

        return $sku;
    }

    public function statusToHumanRead($val): string
    {
        switch ($val) {
            case self::STATUS_TO_DO:
                return 'To Do';
            case self::STATUS_DOING:
                return 'Doing';
            case self::STATUS_COMPLETED:
                return 'Completed';
            case self::STATUS_TRANSFERRED:
                return 'Transferred';
        }
    }

    /**
     * Return in percentage
     */
    public function getProgress(Production $production)
    {
        $milestone_all_count = ProductionMilestone::where('production_id', $production->id)->count();
        $milestone_completed_count = ProductionMilestone::where('production_id', $production->id)->whereNotNull('submitted_at')->count();

        $progress = number_format(($milestone_completed_count / $milestone_all_count) * 100, 2);

        if (str_contains($progress, '.00')) {
            $progress = (int) $progress;
        }

        return $progress;
    }
}
