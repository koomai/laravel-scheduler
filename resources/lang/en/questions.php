<?php

return [
    'type' => 'Select type of scheduled task',
    'task' => [
        'artisan' => 'Enter your artisan command with arguments and options, e.g. `telescope:prune --hours=24`',
        'job' => 'Enter the fully qualified classname of the job you would like to schedule, e.g. `App\Jobs\SendEmail`',
    ],
    'queue' => 'If this job needs to be dispatched to a specific queue, enter the queue name or leave empty',
    'description' => 'Enter a short description (30 characters or less)',
    'cron' => 'Enter the cron expression for your task, e.g. `0 12 * * 5`. Check out https://crontab.guru if you need help',
    'timezone' => 'Enter a timezone or leave empty to use default timezone',
    'environments' => 'Enter a comma-separated list of environments this task should run in, e.g. `prod,staging`. Leave empty to run in all environments.',
    'overlapping' => 'Run tasks without overlapping?',
    'maintenance' => 'Run task even in maintenance mode?',
    'one_server' => 'Run task only on one server? Note: You must be using redis or memcached as your default cache driver',
    'background' => 'Run task in background',
    'confirm_output_path' => 'Do you want to send the output of this task to a file?',
    'output_path' => 'Enter file path',
    'append_output' => 'Append output to file?',
    'output_email' => 'Enter an email address to email output to or leave empty',
];
