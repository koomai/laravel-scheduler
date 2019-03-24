<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scheduled Tasks Table Name
    |--------------------------------------------------------------------------
    |
    | The table where the scheduled tasks will be stored.
    |
    */

    'table' => 'scheduled_tasks',

    /*
    |--------------------------------------------------------------------------
    | Number of Cron Attempts
    |--------------------------------------------------------------------------
    |
    | The number of tries a user gets to enter a valid cron expression
    | before an exit error.
    |
    */

    'cron_attempts' => 3,

    /*
    |--------------------------------------------------------------------------
    | Environments where scheduled tasks are allowed
    |--------------------------------------------------------------------------
    |
    | Restrict scheduled tasks to specific environments only.
    | Leave empty to allow on all or user-specified environments.
    |
    */

    'environments' => [],

    /*
    |--------------------------------------------------------------------------
    | Date format
    |--------------------------------------------------------------------------
    |
    | Display format for due date/time for scheduled tasks
    |
    */
    'date_format' => 'H:i:s \o\n l, d M Y \(e\)',
];
