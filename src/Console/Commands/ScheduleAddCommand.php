<?php

namespace Koomai\Scheduler\Console\Commands;

use DateTimeZone;
use Illuminate\Support\Facades\Artisan;
use Koomai\Scheduler\Console\Commands\Traits\ValidatesInput;
use Koomai\Scheduler\Constants\TaskType;
use Koomai\Scheduler\Console\Commands\Traits\BuildsScheduledTasksTable;
use Koomai\Scheduler\ScheduledTask;

class ScheduleAddCommand extends ScheduleCommand
{
    use BuildsScheduledTasksTable,
        ValidatesInput;

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

    private $type;
    private $task;
    private $taskDescription;
    private $cron;
    private $timezone;
    private $queue;
    private $environments;
    private $withoutOverlapping;
    private $onOneServer;
    private $inMaintenanceMode;
    private $runInBackground;
    private $outputPath;
    private $appendOutput = false;
    private $outputEmail;

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \ReflectionException
     * @throws \Koomai\Constants\InvalidConstantException
     */
    public function handle()
    {
        if ($this->option('type') && $this->option('task') && $this->option('cron')) {
            return $this->handleWithoutPrompts();
        }

        return $this->handleWithPrompts();
    }

    /**
     * Execute the console command by parsing options
     *
     * @return mixed
     * @throws \Koomai\Constants\InvalidConstantException
     */
    private function handleWithoutPrompts()
    {
        if (! $this->validate($this->options())) {
            foreach ($this->errors as $error) {
                $this->error($error);
            }

            return 1;
        }

        $this->type = $this->option('type');
        $this->task = $this->option('task');
        $this->taskDescription = $this->option('description');
        $this->cron = $this->option('cron');
        $this->timezone = $this->option('timezone');
        $this->environments = config('scheduler.environments') ?:
            ($this->option('environments') === null
                ? []
                : explode(',', $this->option('environments'))
            );
        $this->withoutOverlapping = config('scheduler.without_overlapping') ?? $this->option('without-overlapping');
        $this->onOneServer = config('scheduler.on_one_server') ?? $this->option('on-one-server');
        $this->inMaintenanceMode = config('scheduler.in_maintenance_mode') ?? $this->option('in-maintenance-mode');
        $this->runInBackground = config('scheduler.run_in_background') ?? $this->option('run-in-background');
        $this->outputPath = config('scheduler.output_path') ?? $this->option('output-path');
        $this->appendOutput = $this->option('append-output');
        $this->outputEmail = config('scheduler.output_email') ?? $this->option('output-email');

        $scheduledTask = $this->createTask();
        $this->generateTable($scheduledTask);
    }

    /**
     * Execute the console command by prompting the user for options
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function handleWithPrompts()
    {
        $this->type = $this->choice(trans('scheduler::questions.type'), TaskType::values());

        $this->task = $this->askForTask();

        if (!$this->task) {
            $this->error(trans('scheduler::messages.invalid_task_type', ['type' => $this->type]));

            return 1;
        }

        $this->taskDescription = $this->ask(trans('scheduler::questions.description'));
        $this->cron = $this->askForCronExpression();
        $this->timezone = $this->askForTimezone();
        $this->environments = config('scheduler.environments') ?: $this->askForEnvironments();
        $this->withoutOverlapping = config('scheduler.without_overlapping') ?? $this->askIfTaskShouldRunWithoutOverlapping();
        $this->onOneServer = config('scheduler.on_one_server') ?? $this->askIfTaskShouldRunOnOneServer();
        $this->inMaintenanceMode = config('scheduler.in_maintenance_mode') ?? $this->askIfTaskShouldRunInMaintenanceMode();
        $this->runInBackground = config('scheduler.run_in_background') ?? $this->askIfTaskShouldRunInBackground();
        $this->outputPath = config('scheduler.output_path') ?? $this->askForOutputFilePath();
        $this->outputEmail = config('scheduler.output_email') ?? $this->askForOutputEmail();

        $scheduledTask = $this->createTask();
        $this->generateTable($scheduledTask);
    }

    private function askForTask(): ?string
    {
        switch ($this->type) {
            case TaskType::COMMAND:
                $task = $this->askForArtisanCommandTask();
                break;
            case TaskType::JOB:
                $task = $this->askForJobTask();
                break;
            default:
                $task = null;
        }

        return $task;
    }

    private function askForArtisanCommandTask(): string
    {
        $command = $this->anticipate(trans('scheduler::questions.task.artisan'), array_keys(Artisan::all()));

        while (! $this->isValidArtisanCommand($command)) {
            $this->warn(trans('scheduler::messages.invalid_artisan_command', ['command' => $command]));
            $command = $this->anticipate(trans('scheduler::questions.task.artisan'), array_keys(Artisan::all()));
        }

        return $command;
    }

    private function askForJobTask(): string
    {
        $job = $this->ask(trans('scheduler::questions.task.job'));

        while (! $this->isValidJob($job)) {
            $this->warn(trans('scheduler::messages.invalid_job_class', ['job' => $job]));
            $job = $this->ask(trans('scheduler::questions.task.job'));
        }

        $this->queue = $this->ask(trans('scheduler::questions.queue'));

        return $job;
    }

    private function askForCronExpression(): string
    {
        $cron = $this->ask(trans('scheduler::questions.cron'));

        while (! $this->isValidCronExpression($cron)) {
            $this->warn(trans('scheduler::messages.invalid_cron_expression', ['cron' => $cron]));
            $cron = $this->ask(trans('scheduler::questions.cron'));
        }

        return $cron;
    }

    private function askForTimezone(): ?string
    {
        $timezone = $this->anticipate(trans('scheduler::questions.timezone'), DateTimeZone::listIdentifiers());

        while ($timezone !== null && ! $this->isValidTimezone($timezone)) {
            $this->warn(trans('scheduler::messages.invalid_timezone', ['timezone' => $timezone]));
            $timezone = $this->anticipate(trans('scheduler::questions.timezone'), DateTimeZone::listIdentifiers());
        }

        return $timezone;
    }

    private function askForEnvironments(): array
    {
        $environments = $this->ask(trans('scheduler::questions.environments'));

        return $environments === null ? [] : explode(',', $environments);
    }

    private function askIfTaskShouldRunWithoutOverlapping(): bool
    {
        return $this->choice(trans('scheduler::questions.overlapping'), ['No', 'Yes']) === 'Yes';
    }

    private function askIfTaskShouldRunOnOneServer(): bool
    {
        $choice = $this->choice(trans('scheduler::questions.one_server'), ['No', 'Yes']) === 'Yes';

        if ($choice) {
            $this->warn(trans('scheduler::messages.cache_driver_alert'));
        }

        return $choice;
    }

    private function askIfTaskShouldRunInMaintenanceMode(): bool
    {
        return $this->choice(trans('scheduler::questions.maintenance'), ['No', 'Yes']) === 'Yes';
    }

    private function askIfTaskShouldRunInBackground(): bool
    {
        return $this->choice(trans('scheduler::questions.background'), ['No', 'Yes']) === 'Yes';
    }

    private function askForOutputFilePath(): ?string
    {
        if ($this->type !== TaskType::JOB && $this->confirm(trans('scheduler::questions.confirm_output_path'))) {
            $outputFilePath = $this->ask(trans('scheduler::questions.output_path'));
            $this->appendOutput = $this->choice(trans('scheduler::questions.append_output'), ['No', 'Yes']) === 'Yes';

            return $outputFilePath;
        }

        return null;
    }

    private function askForOutputEmail(): ?string
    {
        if ($this->type !== TaskType::JOB) {
            return $this->ask(trans('scheduler::questions.output_email'));
        }

        return null;
    }

    /**
     * @return \Koomai\Scheduler\ScheduledTask
     */
    private function createTask(): ScheduledTask
    {
        return $this->repository->create(
            [
                'type' => $this->type,
                'task' => $this->task,
                'description' => $this->taskDescription,
                'cron' => $this->cron,
                'timezone' => $this->timezone,
                'environments' => $this->environments,
                'queue' => $this->queue,
                'without_overlapping' => $this->withoutOverlapping,
                'on_one_server' => $this->onOneServer,
                'run_in_background' => $this->runInBackground,
                'in_maintenance_mode' => $this->inMaintenanceMode,
                'output_path' => $this->outputPath,
                'append_output' => $this->appendOutput,
                'output_email' => $this->outputEmail,
            ]
        );
    }
}
