<?php

namespace Koomai\Scheduler\Console\Commands\Traits;

use Cron\CronExpression;
use DateTimeZone;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Koomai\Scheduler\Constants\TaskType;
use Koomai\Scheduler\Rules\CronExpressionRule;
use Koomai\Scheduler\Rules\TaskTypeRule;

trait ValidatesInput
{
    private function isValidTaskType($type): bool
    {
        return in_array($type, TaskType::values());
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
        return in_array($timezone, DateTimeZone::listIdentifiers());
    }
}
