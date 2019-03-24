<?php

namespace Koomai\Scheduler\Console\Commands;

use Illuminate\Console\Command;
use Koomai\Scheduler\Contracts\ScheduledTaskRepositoryInterface;

abstract class ScheduleCommand extends Command
{
    /**
     * @var \Koomai\Scheduler\Contracts\ScheduledTaskRepositoryInterface
     */
    protected $repository;

    /**
     * Create a new command instance.
     *
     * @param \Koomai\Scheduler\Contracts\ScheduledTaskRepositoryInterface $repository
     */
    public function __construct(ScheduledTaskRepositoryInterface $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }
}
