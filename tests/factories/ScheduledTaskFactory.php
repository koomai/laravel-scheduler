<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Koomai\Scheduler\Constants\TaskType;
use Koomai\Scheduler\ScheduledTask;

$factory->define(
    ScheduledTask::class,
    function (Faker\Generator $faker) {
        return [
            'type' => TaskType::COMMAND,
            'task' => 'cache:clear --quiet',
            'description' => 'Test description',
            'cron' => '* * * * *',
            'timezone' => 'Australia/Sydney',
            'environments' => '[]',
        ];
    }
);
