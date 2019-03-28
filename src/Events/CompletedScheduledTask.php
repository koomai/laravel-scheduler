<?php

namespace Koomai\Scheduler\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Koomai\Scheduler\ScheduledTask;

class CompletedScheduledTask
{
    use Dispatchable, SerializesModels;

    /**
     * @var \Koomai\Scheduler\ScheduledTask
     */
    public $task;

    /**
     * Create a new event instance.
     *
     * @param \Koomai\Scheduler\ScheduledTask $task
     */
    public function __construct(ScheduledTask $task)
    {
        $this->task = $task;
    }
}
