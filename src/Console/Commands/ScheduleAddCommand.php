<?php

namespace Koomai\Scheduler\Console\Commands;

use DateTimeZone;
use Cron\CronExpression;
use Illuminate\Support\Facades\Artisan;
use Koomai\Scheduler\Constants\TaskType;
use Koomai\Scheduler\Console\Commands\Traits\BuildsScheduledTasksTable;

class ScheduleAddCommand extends ScheduleCommand
{
    use BuildsScheduledTasksTable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add an artisan command or a job as scheduled task';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $queue;

    /**
     * @var bool
     */
    private $appendOutput = false;

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function handle()
    {
        $this->type = $this->choice(trans('scheduler::questions.type'), TaskType::values());
        $task = $this->askForTask();
        $description = $this->ask(trans('scheduler::questions.description'));
        $cron = $this->askForCronExpression();
        $timezone = $this->askForTimezone();
        $environments = config('scheduler.environments') ?: $this->askForEnvironments();
        $withoutOverlapping = config('scheduler.without_overlapping') ?? $this->askIfTaskShouldRunWithoutOverlapping();
        $onOneServer = config('scheduler.on_one_server') ?? $this->askIfTaskShouldRunOnOneServer();
        $runInBackground = config('scheduler.run_in_background') ?? $this->askIfTaskShouldRunInBackground();
        $inMaintenanceMode = config('scheduler.in_maintenance_mode') ?? $this->askIfTaskShouldRunInMaintenanceMode();
        $outputPath = config('scheduler.output_path') ?? $this->askForOutputFilePath();
        $outputEmail = config('scheduler.output_email') ?? $this->askForOutputEmail();

        $task = $this->repository->create(
            [
                'type' => $this->type,
                'task' => $task,
                'description' => $description,
                'cron' => $cron,
                'timezone' => $timezone,
                'environments' => $environments,
                'queue' => $this->queue,
                'without_overlapping' => $withoutOverlapping,
                'on_one_server' => $onOneServer,
                'run_in_background' => $runInBackground,
                'in_maintenance_mode' => $inMaintenanceMode,
                'output_path' => $outputPath,
                'append_output' => $this->appendOutput,
                'output_email' => $outputEmail,
            ]
        );

        $this->generateTable($task);
    }

    private function askForTask()
    {
        switch ($this->type) {
            case TaskType::ARTISAN:
                $task = $this->askForArtisanTask();
                break;
            case TaskType::JOB:
                $task = $this->askForJobTask();
                break;
            default:
                $this->warn('Invalid scheduled task type');
                exit();
        }

        return $task;
    }

    private function askForArtisanTask()
    {
        $artisanCommands = array_keys(Artisan::all());

        $task = $this->anticipate(trans('scheduler::questions.task.artisan'), $artisanCommands);

        if (! $this->isValidArtisanCommand($task, $artisanCommands)) {
            $this->error("`{$task}` is not a valid artisan command. Please start again");
            exit();
        }

        return $task;
    }

    private function askForJobTask()
    {
        $job = $this->ask(trans('scheduler::questions.job'));

        if (! $this->isValidJob($job)) {
            $this->warn("`{$job}` class does not exist. Please try again");
            exit();
        }

        $this->queue = $this->ask(trans('scheduler::questions.queue'));

        return $job;
    }

    private function isValidArtisanCommand($task, $artisanCommands)
    {
        return in_array(strtok($task, ' '), $artisanCommands);
    }

    private function isValidJob($job)
    {
        return class_exists($job);
    }

    private function askForCronExpression($tries = 1)
    {
        $cron = $this->ask(trans('scheduler::questions.cron'));

        $allowed = config('scheduler.cron_attempts', 1);

        if (! CronExpression::isValidExpression($cron) && $tries < $allowed) {
            $this->warn("{$cron} is an invalid cron expression. Please try again");
            $tries++;
            $cron = $this->askForCronExpression($tries);
        }

        if (! CronExpression::isValidExpression($cron) && $tries >= $allowed) {
            $this->error("{$cron} is an invalid cron expression. Exiting...");
            exit();
        }

        return $cron;
    }

    private function askForTimezone()
    {
        return $this->anticipate(trans('scheduler::questions.timezone'), DateTimeZone::listIdentifiers());
    }

    private function askForEnvironments()
    {
        $environments = $this->ask(trans('scheduler::questions.environments'));

        return is_null($environments) ? [] : explode(',', $environments);
    }

    private function askIfTaskShouldRunWithoutOverlapping()
    {
        return $this->choice(trans('scheduler::questions.overlapping'), ['No', 'Yes']) === 'Yes'
                    ? true
                    : false;
    }

    private function askIfTaskShouldRunInMaintenanceMode()
    {
        return $this->choice(trans('scheduler::questions.maintenance'), ['No', 'Yes']) === 'Yes'
            ? true
            : false;
    }

    private function askIfTaskShouldRunOnOneServer()
    {
        $choice = $this->choice(trans('scheduler::questions.one_server'), ['No', 'Yes']) === 'Yes'
            ? true
            : false;

        if ($choice) {
            $this->alert('Ensure your default cache driver is redis or memcached – https://laravel.com/docs/scheduling#running-tasks-on-one-server');
        }

        return $choice;
    }

    private function askIfTaskShouldRunInBackground()
    {
        return $this->choice(trans('scheduler::questions.background'), ['No', 'Yes']) === 'Yes'
            ? true
            : false;
    }

    private function askForOutputFilePath()
    {
        if ($this->type !== TaskType::JOB && $this->confirm(trans('scheduler::questions.confirm_output_path'))) {
            $outputFilePath = $this->ask(trans('scheduler::questions.output_path'));
            $this->appendOutput = $this->choice(trans('scheduler::questions.append_output'), ['No', 'Yes']) === 'Yes'
                ? true
                : false;

            return $outputFilePath;
        }
    }

    private function askForOutputEmail()
    {
        if ($this->type === TaskType::JOB) {
            return;
        }

        return $this->ask(trans('scheduler::questions.output_email'));
    }
}
