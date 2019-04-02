<?php

return [
    'required_options' => '--type, --task and --cron are required options. See --help or documentation for all available options',
    'invalid_task_type' => '[:type] is an invalid scheduled task type. Valid types are – command and job.',
    'invalid_artisan_command' => '[:command] not a valid artisan command. Please try again. Use --help flag for more details.',
    'invalid_job_class' => '[:job] class does not exist. Please try again.',
    'invalid_cron_expression' => '[:cron] is an invalid cron expression. Please try again.',
    'invalid_timezone' => '[:timezone] is an invalid timezone. Leave empty to use default timezone.',
    ];
