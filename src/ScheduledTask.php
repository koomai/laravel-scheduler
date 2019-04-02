<?php

namespace Koomai\Scheduler;

use Illuminate\Database\Eloquent\Model;

class ScheduledTask extends Model
{
    protected $guarded = [];

    protected $casts = [
        'environments' => 'array',
        'without_overlapping' => 'boolean',
        'on_one_server' => 'boolean',
        'run_in_background' => 'boolean',
        'even_in_maintenance_mode' => 'boolean',
        'append_output' => 'boolean',
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('scheduler.table');
    }

    /**
     * Save type as defined in Koomai\Scheduler\Constants\TaskType::class
     *
     * @param $value
     */
    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = ucfirst(strtolower($value));
    }
}
