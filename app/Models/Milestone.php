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

    const LIST = [
        self::TYPE_SERVICE_TASK => [
            'Check In',
            'Photo of equipment',
            'Before Service',
            'After Service',
            'Service From Cop Sign',
            'Bill Payment',
            'Part Replacement',
            'Check Out'
        ],
        self::TYPE_DRIVER_TASK => [
            'Pick Up at Warehouse',
            'Check in',
            'Unloaded at customer',
            'Installed & Briefing Customer',
            'Revise Inspection & Feedback from Customer',
            'DO Cop sign',
            'Payment Collection',
            'Check Out',
        ],
        self::TYPE_DRIVER_RETURN_TASK => [
            'Check in',
            'Take Photo of defect',
            'Uninstalled the product',
            'Loaded to lorry',
            'Feedback From Customer',
            'Customer sign',
            'Check Out'
        ],
        self::TYPE_INSTALLER_TASK => [
            'Check in',
            'Before installation',
            'After installation',
            'Testing & Commission Form Cop Sign',
            'Check out',
        ],
        self::TYPE_SITE_VISIT => [
            'Check in',
            'Measurement Remark (Attach Photo)',
            'Survey feedback',
            'Check out',
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
        return $this->belongsToMany(Task::class, 'task_milestone', 'milestone_id', 'task_id');
    }
}
