<?php

namespace Koomai\Scheduler\Console\Commands;

use Koomai\Scheduler\Console\Commands\Traits\BuildsScheduledTasksTable;
use Koomai\Scheduler\Console\Commands\Traits\ValidatesInput;
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
    protected $signature = 'schedule:add {--type= : [Required] The type of scheduled task. Options: job or command}
                {--task= : [Required] Command with arguments/options or fully qualified Jobs classname }
                {--description= : Scheduled task description in 30 characters}
                {--cron= : [Required] Cron expression for schedule. Check out https://crontab.guru if you need help}
                {--timezone= : Timezone for scheduled task}
                {--environments= : Comma-separated list of environments the task should run in}
                {--queue= : Queue name if scheduled job needs to run on a specific queue}
                {--without-overlapping : Set this flag if the task should run without overlapping}
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
    private $inMaintenanceMode;
    private $runInBackground;
    private $outputPath;
    private $appendOutput = false;
    private $outputEmail;

    /**
     * Execute the console command by parsing options
     *
     * @return mixed
     */
    public function handle()
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
        $this->inMaintenanceMode = config('scheduler.in_maintenance_mode') ?? $this->option('in-maintenance-mode');
        $this->runInBackground = config('scheduler.run_in_background') ?? $this->option('run-in-background');
        $this->outputPath = config('scheduler.output_path') ?? $this->option('output-path');
        $this->appendOutput = $this->option('append-output');
        $this->outputEmail = config('scheduler.output_email') ?? $this->option('output-email');

        $scheduledTask = $this->createTask();
        $this->generateTable($scheduledTask);
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
                'run_in_background' => $this->runInBackground,
                'in_maintenance_mode' => $this->inMaintenanceMode,
                'output_path' => $this->outputPath,
                'append_output' => $this->appendOutput,
                'output_email' => $this->outputEmail,
            ]
        );
    }
}
