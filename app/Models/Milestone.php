<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    const TYPE_SERVICE_TASK = 1;
    const TYPE_DRIVER_TASK = 2;
    const TYPE_DRIVER_RETURN_TASK = 3;
    const TYPE_INSTALLER_TASK = 4;
    const TYPE_SITE_VISIT = 5;
    const TYPE_PRODUCTION = 6;

    const LIST = [
        self::TYPE_SERVICE_TASK => [
            'Check In',
            'Photo of Equipment',
            'Before Service',
            'After Service',
            'Service From Cop Sign',
            'Part Replacement',
            'Payment Collection',
            'Check Out'
        ],
        self::TYPE_DRIVER_TASK => [
            'Pick Up at Warehouse',
            'Check In',
            'Unloaded at Customer',
            'Installed & Briefing Customer',
            'Revise Inspection & Feedback from Customer',
            'DO Cop sign',
            'Payment Collection',
            'Check Out',
        ],
        self::TYPE_DRIVER_RETURN_TASK => [
            'Check In',
            'Take Photo of defect',
            'Uninstalled the Product',
            'Loaded to Lorry',
            'Feedback from Customer',
            'Customer Sign',
            'Payment Collection',
            'Check Out'
        ],
        self::TYPE_INSTALLER_TASK => [
            'Check In',
            'Before Installation',
            'After Installation',
            'Testing & Commission Form Cop Sign',
            'Check Out',
            'Payment Collection',
        ],
        self::TYPE_SITE_VISIT => [
            'Check In',
            'Measurement Remark (Attach Photo)',
            'Survey Feedback',
            'Check Out',
        ]
    ];

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date;
    }

    public function tasks() {
        return $this->belongsToMany(Task::class, 'task_milestone', 'milestone_id', 'task_id')
            ->withPivot('address', 'datetime', 'amount_collected', 'remark', 'submitted_at')
            ->using(TaskMilestone::class);
    }
}
