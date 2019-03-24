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
        $this->type = $this->choice('Select type of scheduled task', TaskType::values());
        $task = $this->askForTask();
        $description = $this->ask('Enter a short description (30 characters or less)');
        $cron = $this->askForCronExpression();
        $timezone = $this->askForTimezone();
        $environments = config('scheduler.environments') ?: $this->askForEnvironments();
        $withoutOverlapping = $this->askIfTaskShouldRunWithoutOverlapping();
        $onOneServer = $this->askIfTaskShouldRunOnOneServer();
        $runInBackground = $this->askIfTaskShouldRunInBackground();
        $inMaintenanceMode = $this->askIfTaskShouldRunInMaintenanceMode();
        $outputPath = $this->askForOutputFilePath();
        $outputEmail = $this->askForOutputEmail();

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
                $task = $this->artisanTaskHandler();
                break;
            case TaskType::JOB:
                $task = $this->jobTaskHandler();
                break;
            default:
                $this->warn('Invalid scheduled task type');
                exit();
        }

        return $task;
    }

    private function artisanTaskHandler()
    {
        $artisanCommands = array_keys(Artisan::all());

        $task = $this->anticipate(
            'Enter your artisan command with arguments and options, e.g. `telescope:prune --hours=24`',
            $artisanCommands
        );

        if (! $this->isValidArtisanCommand($task, $artisanCommands)) {
            $this->error("`{$task}` is not a valid artisan command. Please start again");
            exit();
        }

        return $task;
    }

    private function jobTaskHandler()
    {
        $job = $this->ask("Enter the fully qualified classname of the job you would like to schedule, e.g. `App\Jobs\SendEmail`");

        if (! $this->isValidJob($job)) {
            $this->warn("`{$job}` class does not exist. Please try again");
            exit();
        }

        $this->queue = $this->ask('If this job needs to be dispatched to a specific queue, enter the queue name or leave empty');

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
        $cron = $this->ask('Enter the cron expression for your task, e.g. `0 12 * * 5`. Check out https://crontab.guru if you need help');

        $allowed = config('scheduler.cron_attempts');

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
        return $this->anticipate(
            'Enter a timezone or leave empty to use default timezone',
            DateTimeZone::listIdentifiers()
        );
    }

    private function askForEnvironments()
    {
        $environments = $this->ask('Enter a comma-separated list of environments this task should run in, e.g. `prod,staging`. Leave empty to run in all environments.');

        return is_null($environments) ? [] : explode(',', $environments);
    }

    private function askIfTaskShouldRunWithoutOverlapping()
    {
        return $this->choice('Run tasks without overlapping?', ['No', 'Yes']) === 'Yes'
                    ? true
                    : false;
    }

    private function askIfTaskShouldRunInMaintenanceMode()
    {
        return $this->choice('Run task even in maintenance mode?', ['No', 'Yes']) === 'Yes'
            ? true
            : false;
    }

    private function askIfTaskShouldRunOnOneServer()
    {
        $choice = $this->choice('Run task only on one server? Note: You must be using redis or memcached as your default cache driver', ['No', 'Yes']) === 'Yes'
            ? true
            : false;

        if ($choice) {
            $this->alert('Ensure your default cache driver is redis or memcached – https://laravel.com/docs/scheduling#running-tasks-on-one-server');
        }

        return $choice;
    }

    private function askIfTaskShouldRunInBackground()
    {
        return $this->choice('Run task in background?', ['No', 'Yes']) === 'Yes'
            ? true
            : false;
    }

    private function askForOutputFilePath()
    {
        if ($this->type !== TaskType::JOB && $this->confirm('Do you want to send the output of this task to a file?')) {
            $outputFilePath = $this->ask('Enter file path');
            $this->appendOutput = $this->choice('Append output to file?', ['No', 'Yes']) === 'Yes'
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

        return $this->ask('Enter an email address to email output to or leave empty');
    }
}
