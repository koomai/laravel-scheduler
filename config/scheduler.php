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
    | Additional prompts
    |--------------------------------------------------------------------------
    | You can set a default value for the properties below to avoid having to
    | type all the options when running schedule:add. Null values trigger prompts.

    | without_overlapping - true/false
    | run_in_background - true/false
    | in_maintenance_mode - true/false
    | output_path - string
    | output_email - string
    */

    'without_overlapping' => null,
    'run_in_background' => null,
    'in_maintenance_mode' => null,
    'output_path' => null,
    'append_output' => null,
    'output_email' => null,

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
