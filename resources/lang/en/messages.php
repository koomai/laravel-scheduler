<?php

return [
    'invalid_task_type' => 'Invalid scheduled task type.',
    'invalid_artisan_command' => '[:task] not a valid artisan command. Please start again.',
    'invalid_job_class' => '[:job] class does not exist. Please try again.',
    'invalid_cron_warn' => '[:cron] is an invalid cron expression. Please try again.',
    'invalid_cron_error' => '[:cron] is an invalid cron expression. Exiting...',
    'cache_driver_alert' => 'Ensure your default cache driver is redis or memcached – https://laravel.com/docs/scheduling#running-tasks-on-one-server',
];
