<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Production extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_TO_DO = 1;
    const STATUS_DOING = 2;
    const STATUS_COMPLETED = 3;
    
    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_production', 'production_id', 'user_id');
    }

    public function product() {
        return $this->hasOne(Product::class);
    }

    public function milestones() {
        return $this->belongsToMany(Milestone::class, 'production_milestone', 'production_id', 'milestone_id')
            ->withPivot('id', 'submitted_at')
            ->using(ProductionMilestone::class);
    }

    public function generateSku(): string {
        $sku = null;
        
        while (true) {
            $sku = 'PO' . now()->format('ym') . generateRandomAlphabet();

            $exists = self::where(DB::raw('BINARY `sku`'), $sku)->exists();

            if (!$exists) {
                break;
            }
        }

        return $sku;
    }

    public function statusToHumanRead($val): string {
        switch ($val) {
            case self::STATUS_TO_DO:
                return 'to do';
            case self::STATUS_DOING:
                return 'doing';
            case self::STATUS_COMPLETED:
                return 'completed';
        }
    }

    /**
     * Return in percentage
     */
    public function getProgress(Production $production) {
        $milestone_all_count = ProductionMilestone::where('production_id', $production->id)->count();
        $milestone_completed_count = ProductionMilestone::where('production_id', $production->id)->whereNotNull('submitted_at')->count();

        $progress = number_format(($milestone_completed_count / $milestone_all_count) * 100, 2);

        if (str_contains($progress, '.00')) {
            $progress = (int)$progress;
        }

        return $progress;
    }
}
