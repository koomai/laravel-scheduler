<?php

namespace Koomai\Scheduler\Contracts;

use Koomai\Scheduler\ScheduledTask;
use Illuminate\Database\Eloquent\Collection;

interface ScheduledTaskRepositoryInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(): Collection;

    /**
     * @param int $id
     *
     * @return \Koomai\Scheduler\ScheduledTask|null
     */
    public function find(int $id): ?ScheduledTask;

    /**
     * @param array $ids
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findByIds(array $ids): ?Collection;

    /**
     * @param array $data
     *
     * @return \Koomai\Scheduler\ScheduledTask
     */
    public function create(array $data): ScheduledTask;

    /**
     * @param int $id
     *
     * @return int
     */
    public function delete(int $id): int;

    /**
     * Checks if the migration has run and the table
     * for this repository has been created.
     *
     * @return bool
     */
    public function hasTable(): bool;

    /**
     * @param $task
     * @param $cron
     *
     * @return \Koomai\Scheduler\ScheduledTask|null
     */
    public function findByTaskAndCronSchedule($task, $cron);
}
