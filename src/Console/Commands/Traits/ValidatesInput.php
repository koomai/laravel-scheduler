<?php

namespace Koomai\Scheduler\Console\Commands\Traits;

use Cron\CronExpression;
use DateTimeZone;
use Illuminate\Support\Facades\Artisan;
use Koomai\Scheduler\Constants\TaskType;

trait ValidatesInput
{
    /** @var array */
    private $errors = [];

    private function validate(array $options)
    {
        [
            'type' => $type,
            'task' => $task,
            'cron' => $cron,
            'timezone' => $timezone,
        ] = $options;

        if (! $this->isValidTaskType($type)) {
            $this->errors[] = trans('scheduler::messages.invalid_task_type', ['type' => $type]);
        }

        if (strtolower($type) === strtolower(TaskType::COMMAND) && !$this->isValidArtisanCommand($task)) {
            $this->errors[] = trans('scheduler::messages.invalid_artisan_command', ['command' => $task]);
        }

        if (strtolower($type) === strtolower(TaskType::JOB) && !$this->isValidJob($task)) {
            $this->errors[] = trans('scheduler::messages.invalid_job_class', ['job' => $task]);
        }

        if (! $this->isValidCronExpression($cron)) {
            $this->errors[] = trans('scheduler::messages.invalid_cron_expression', ['cron' => $cron]);
        }

        if ($timezone !== null && !$this->isValidTimezone($timezone)) {
            $this->errors[] = trans('scheduler::messages.invalid_timezone', ['timezone' => $timezone]);
        }

        return empty($this->errors);
    }

    private function isValidTaskType($type): bool
    {
        return in_array(strtolower($type), array_map('strtolower', TaskType::values()), true);
    }

    private function isValidArtisanCommand($task): bool
    {
        return array_key_exists(strtok($task, ' '), Artisan::all());
    }

    private function isValidJob($job): bool
    {
        return class_exists($job);
    }

    private function isValidCronExpression($cron): bool
    {
        return CronExpression::isValidExpression($cron);
    }

    private function isValidTimezone($timezone): bool
    {
        return in_array($timezone, DateTimeZone::listIdentifiers(), true);
    }
}
