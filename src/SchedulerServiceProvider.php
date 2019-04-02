<?php

namespace Koomai\Scheduler;

use Illuminate\Support\ServiceProvider;
use Koomai\Scheduler\Constants\TaskType;
use Illuminate\Console\Scheduling\Schedule;
use Koomai\Scheduler\Console\Commands\ScheduleAddCommand;
use Koomai\Scheduler\Console\Commands\ScheduleDueCommand;
use Koomai\Scheduler\Console\Commands\ScheduleListCommand;
use Koomai\Scheduler\Console\Commands\ScheduleShowCommand;
use Koomai\Scheduler\Events\CompletedScheduledTask;
use Koomai\Scheduler\Events\StartingScheduledTask;
use Koomai\Scheduler\Repositories\ScheduledTaskRepository;
use Koomai\Scheduler\Console\Commands\ScheduleDeleteCommand;
use Koomai\Scheduler\Contracts\ScheduledTaskRepositoryInterface;
use Koomai\Scheduler\Repositories\Cache\CacheScheduledTaskRepository;

class SchedulerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
                __DIR__.'/../config/scheduler.php' => config_path('questions.php'),
            ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/', 'scheduler');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ScheduleDueCommand::class,
                ScheduleAddCommand::class,
                ScheduleShowCommand::class,
                ScheduleDeleteCommand::class,
                ScheduleListCommand::class,
            ]);
        }

        // Retrieve and register scheduled tasks from the repository only if the table exists
        $repository = resolve(ScheduledTaskRepositoryInterface::class);
        if ($repository->hasTable()) {
            $this->scheduleTasks($repository->all());
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/scheduler.php', 'scheduler');

        $this->app->singleton(ScheduledTaskRepositoryInterface::class, function () {
            $scheduledTaskRepository = new ScheduledTaskRepository();

            return new CacheScheduledTaskRepository($scheduledTaskRepository, $this->app['cache.store']);
        });
    }

    private function scheduleTasks($tasks)
    {
        $this->app->resolving(Schedule::class, function ($schedule) use ($tasks) {
            $tasks->each(function ($task) use ($schedule) {
                switch ($task->type) {
                    case TaskType::JOB:
                        if ($task->queue) {
                            $scheduledEvent = $schedule->job(new $task->task, $task->queue);
                        } else {
                            $scheduledEvent = $schedule->job(new $task->task);
                        }
                        break;
                    case TaskType::COMMAND:
                        $scheduledEvent = $schedule->command($task->task);
                        break;
                    default:
                        app('log')->alert("{$task->type} is not a valid scheduled task type");

                        return;
                }

                /* @var \Illuminate\Console\Scheduling\Event $scheduledEvent */
                $scheduledEvent
                    ->cron($task->cron)
                    ->timezone($task->timezone ?? config('app.timezone'))
                    ->before(function() use ($task) {
                        StartingScheduledTask::dispatch($task);
                    })
                    ->after(function () use ($task) {
                        CompletedScheduledTask::dispatch($task);
                    });

                if (! empty($task->environments)) {
                    $scheduledEvent->environments($task->environments);
                }

                if ($task->without_overlapping) {
                    $scheduledEvent->withoutOverlapping();
                }

                if ($task->run_in_background) {
                    $scheduledEvent->runInBackground();
                }

                if ($task->in_maintenance_mode) {
                    $scheduledEvent->evenInMaintenanceMode();
                }

                if ($task->output_path) {
                    $scheduledEvent->sendOutputTo($task->output_path, $task->append_output);
                }

                if ($task->output_email) {
                    $scheduledEvent->emailOutputTo($task->output_email);
                }
            });
        });
    }
}
