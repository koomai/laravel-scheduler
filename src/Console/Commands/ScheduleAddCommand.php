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
    protected $signature = 'schedule:add {--type= : The type of scheduled task. Options: job or command}
                {--task= : Command with arguments/options or fully qualified Jobs classname }
                {--description= : Scheduled task description in 30 characters}
                {--cron= : Cron expression for schedule. Check out https://crontab.guru if you need help}
                {--timezone= : Timezone for scheduled task}
                {--environments= : Comma-separated list of environments the task should run in}
                {--queue= : Queue name if scheduled job needs to run on a specific queue}
                {--without-overlapping : Set this flag if the task should run without overlapping}
                {--on-one-server : Set this flag if the task should run on one server only. Requires redis/memcached cache driver}
                {--run-in-background : Set this flag if the task should run in the background}
                {--in-maintenance-mode : Set this flag if the task should run even in maintenance mode}
                {--output-path= : Add path to file where output should be sent to}
                {--append-output : Set flag if the output should be appended to the file}
                {--output-email= : Add email address if output should be sent via email}';
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
        if ($this->option('type') && $this->option('task') && $this->option('cron')) {
            return $this->handleWithoutPrompts();
        }

        $this->type = $this->choice(trans('scheduler::questions.type'), TaskType::values());

        $task = $this->askForTask();
        if (!$task) {
            return 1;
        }

        $description = $this->ask(trans('scheduler::questions.description'));

        $cron = $this->askForCronExpression();
        if (!$cron) {
            return 1;
        }

        $timezone = $this->askForTimezone();
        $environments = config('scheduler.environments') ?: $this->askForEnvironments();
        $withoutOverlapping = config('scheduler.without_overlapping') ?? $this->askIfTaskShouldRunWithoutOverlapping();
        $onOneServer = config('scheduler.on_one_server') ?? $this->askIfTaskShouldRunOnOneServer();
        $inMaintenanceMode = config('scheduler.in_maintenance_mode') ?? $this->askIfTaskShouldRunInMaintenanceMode();
        $runInBackground = config('scheduler.run_in_background') ?? $this->askIfTaskShouldRunInBackground();
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

    private function handleWithoutPrompts()
    {
        if (!$this->isValidTaskType()) {
            $this->warn(trans('scheduler::messages.invalid_task_type'));
            return 1;
        }

    }

    private function askForTask()
    {
        switch ($this->type) {
            case TaskType::COMMAND:
                $task = $this->askForArtisanTask();
                break;
            case TaskType::JOB:
                $task = $this->askForJobTask();
                break;
            default:
                $task = null;
                $this->warn(trans('scheduler::messages.invalid_task_type'));
        }

        return $task;
    }

    private function askForArtisanTask()
    {
        $artisanCommands = array_keys(Artisan::all());

        $task = $this->anticipate(trans('scheduler::questions.task.artisan'), $artisanCommands);

        if (! $this->isValidArtisanCommand($task, $artisanCommands)) {
            $this->error(trans('scheduler::messages.invalid_artisan_command', ['task' => $task]));
            return;
        }

        return $task;
    }

    private function askForJobTask()
    {
        $job = $this->ask(trans('scheduler::questions.task.job'));

        if (! $this->isValidJob($job)) {
            $this->error(trans('scheduler::messages.invalid_job_class', ['job' => $job]));
            return;
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

    private function askForCronExpression()
    {
        $cron = $this->ask(trans('scheduler::questions.cron'));

        if (! CronExpression::isValidExpression($cron)) {
            $this->warn(trans('scheduler::messages.invalid_cron_warn', ['cron' => $cron]));

            $cron = $this->ask(trans('scheduler::questions.cron'));

            if (! CronExpression::isValidExpression($cron)) {
                $this->error(trans('scheduler::messages.invalid_cron_error', ['cron' => $cron]));
                return;
            }
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

        return $environments === null ? [] : explode(',', $environments);
    }

    private function askIfTaskShouldRunWithoutOverlapping()
    {
        return $this->choice(trans('scheduler::questions.overlapping'), ['No', 'Yes']) === 'Yes';
    }

    private function askIfTaskShouldRunOnOneServer()
    {
        $choice = $this->choice(trans('scheduler::questions.one_server'), ['No', 'Yes']) === 'Yes';

        if ($choice) {
            $this->warn(trans('scheduler::messages.cache_driver_alert'));
        }

        return $choice;
    }

    private function askIfTaskShouldRunInMaintenanceMode()
    {
        return $this->choice(trans('scheduler::questions.maintenance'), ['No', 'Yes']) === 'Yes';
    }

    private function askIfTaskShouldRunInBackground()
    {
        return $this->choice(trans('scheduler::questions.background'), ['No', 'Yes']) === 'Yes';
    }

    private function askForOutputFilePath()
    {
        if ($this->type !== TaskType::JOB && $this->confirm(trans('scheduler::questions.confirm_output_path'))) {
            $outputFilePath = $this->ask(trans('scheduler::questions.output_path'));
            $this->appendOutput = $this->choice(trans('scheduler::questions.append_output'), ['No', 'Yes']) === 'Yes';

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

    /**
     * @return bool
     * @throws \ReflectionException
     */
    private function isValidTaskType(): bool
    {
        return !in_array(strtolower($this->option('type')), array_map('strtolower', TaskType::keys()));
    }
}
