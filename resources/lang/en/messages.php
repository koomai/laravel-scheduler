<?php

return [
    'invalid_task_type' => '[:type] is an invalid scheduled task type. Valid types are – command and job.',
    'invalid_artisan_command' => '[:command] not a valid artisan command. Please try again or press Ctrl+C to exit.',
    'invalid_job_class' => '[:job] class does not exist. Please try again or press Ctrl+C to exit.',
    'invalid_cron_expression' => '[:cron] is an invalid cron expression. Please try again or press Ctrl+C to exit.',
    'invalid_timezone' => '[:timezone] is an invalid timezone. Leave empty to use default timezone. Please try again or press Ctrl+C to exit.',
    'cache_driver_alert' => 'Ensure your default cache driver is redis or memcached – https://laravel.com/docs/scheduling#running-tasks-on-one-server',
];
