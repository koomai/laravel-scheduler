<?php

namespace Koomai\Scheduler\Repositories\Cache;

use Koomai\Scheduler\ScheduledTask;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Collection;
use Koomai\Scheduler\Repositories\ScheduledTaskRepository;
use Koomai\Scheduler\Contracts\ScheduledTaskRepositoryInterface;

class CacheScheduledTaskRepository implements ScheduledTaskRepositoryInterface
{
    /**
     * @var \Koomai\Scheduler\Repositories\ScheduledTaskRepository
     */
    private $repository;

    /**
     * @var \Illuminate\Cache\Repository
     */
    private $cache;

    /**
     * CacheScheduledTaskRepository constructor.
     *
     * @param \Koomai\Scheduler\Repositories\ScheduledTaskRepository $scheduledTaskRepository
     * @param \Illuminate\Cache\Repository $cache
     */
    public function __construct(ScheduledTaskRepository $scheduledTaskRepository, Cache $cache)
    {
        $this->repository = $scheduledTaskRepository;
        $this->cache = $cache;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(): Collection
    {
        return $this->cache->rememberForever('scheduled_tasks.all', function () {
            return $this->repository->all();
        });
    }

    /**
     * @param int $id
     *
     * @return \Koomai\Scheduler\ScheduledTask|null
     */
    public function find(int $id): ?ScheduledTask
    {
        return $this->cache->rememberForever("scheduled_tasks.id.{$id}", function () use ($id) {
            return $this->repository->find($id);
        });
    }

    /**
     * @param array $ids
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findByIds(array $ids): ?Collection
    {
        return $this->repository->findByIds($ids);
    }

    /**
     * @param array $data
     *
     * @return \Koomai\Scheduler\ScheduledTask
     */
    public function create(array $data): ScheduledTask
    {
        $scheduledTask = $this->repository->create($data);
        $this->invalidateCache();

        return $scheduledTask;
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function delete(int $id): int
    {
        $count = $this->repository->delete($id);
        $this->invalidateCache($id);

        return $count;
    }

    /**
     * Removes relevant cached data.
     *
     * @param int $id
     */
    private function invalidateCache(int $id = null): void
    {
        $this->cache->forget('scheduled_tasks.all');

        if ($id) {
            $this->cache->forget("scheduled_tasks.id.{$id}");
        }
    }

    /**
     * @return bool
     */
    public function hasTable(): bool
    {
        return $this->cache->rememberForever('scheduled_tasks.has_table', function () {
            return $this->repository->hasTable();
        });
    }

    /**
     * @param $task
     * @param $cron
     *
     * @return \Koomai\Scheduler\ScheduledTask|null
     */
    public function findByTaskAndCronSchedule($task, $cron)
    {
        return $this->repository->findByTaskAndCronSchedule($task, $cron);
    }
}
