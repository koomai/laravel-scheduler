<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(
    \Koomai\Scheduler\ScheduledTask::class,
    function (Faker\Generator $faker) {
        return [
            'type' => \Koomai\Scheduler\Constants\TaskType::ARTISAN,
            'task' => 'cache:clear --quiet',
            'description' => 'Test description',
            'cron' => '* * * * *',
            'timezone' => 'Australia/Sydney',
            'environments' => '[]',
        ];
    }
);
