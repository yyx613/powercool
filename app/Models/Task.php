<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_DRIVER = 1; 
    const TYPE_TECHNICIAN = 2; 
    const TYPE_SALE = 3; 

    const STATUS_TO_DO = 1;
    const STATUS_DOING = 2;
    const STATUS_IN_REVIEW = 3;
    const STATUS_COMPLETED = 4;

    protected $guarded = [];
    protected $casts = [
        'amount_to_collect' => 'double',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function users() {
        return $this->belongsToMany(User::class, 'user_task', 'task_id', 'user_id');
    }

    public function milestones() {
        return $this->belongsToMany(Milestone::class, 'task_milestone', 'task_id', 'milestone_id')
            ->withPivot('address', 'datetime', 'amount_collected', 'remark', 'submitted_at')
            ->using(TaskMilestone::class);
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'object');
    }

    public function logs() {
        return $this->morphMany(ActivityLog::class, 'object')->orderBy('id', 'desc');
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function generateSku(): string {
        $sku = null;
        
        while (true) {
            $sku = 'DT' . now()->format('ym') . generateRandomAlphabet();

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
            case self::STATUS_IN_REVIEW:
                return 'in review';
            case self::STATUS_COMPLETED:
                return 'completed';
        }
    }

    /**
     * Return in percentage
     */
    public function getProgress(Task $task) {
        $milestone_all_count = TaskMilestone::where('task_id', $task->id)->count();
        $milestone_completed_count = TaskMilestone::where('task_id', $task->id)->whereNotNull('submitted_at')->count();

        return ($milestone_completed_count / $milestone_all_count) * 100;
    }
}
