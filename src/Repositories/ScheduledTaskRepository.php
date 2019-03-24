<?php

namespace Koomai\Scheduler\Repositories;

use Koomai\Scheduler\ScheduledTask;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Collection;
use Koomai\Scheduler\Contracts\ScheduledTaskRepositoryInterface;

class ScheduledTaskRepository implements ScheduledTaskRepositoryInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(): Collection
    {
        return ScheduledTask::all();
    }

    /**
     * @param int $id
     *
     * @return \Koomai\Scheduler\ScheduledTask|null
     */
    public function find(int $id): ?ScheduledTask
    {
        return ScheduledTask::find($id);
    }

    /**
     * @param array $ids
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findByIds(array $ids): ?Collection
    {
        return ScheduledTask::whereIn('id', $ids)->get();
    }

    /**
     * @param $task
     * @param $cron
     *
     * @return \Koomai\Scheduler\ScheduledTask|null
     */
    public function findByTaskAndCronSchedule($task, $cron)
    {
        return ScheduledTask::where('task', $task)->where('cron', $cron)->first();
    }

    /**
     * @param array $data
     *
     * @return \Koomai\Scheduler\ScheduledTask
     */
    public function create(array $data): ScheduledTask
    {
        return ScheduledTask::create($data);
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function delete(int $id): int
    {
        return ScheduledTask::destroy($id);
    }

    /**
     * Checks if the migration has run and the table
     * for this repository has been created.
     *
     * @return bool
     */
    public function hasTable(): bool
    {
        return Schema::hasTable(config('scheduler.table'));
    }
}
