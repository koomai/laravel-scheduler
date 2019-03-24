<?php

namespace Koomai\Scheduler\Console\Commands\Traits;

use Koomai\Scheduler\ScheduledTask;

trait BuildsScheduledTasksTable
{
    protected $headers = [
        'Id',
        'Type',
        'Task',
        'Description',
        'Cron',
        'Timezone',
        'Environments',
        'Queue',
        'Without Overlapping',
        'On One Server',
        'Run in Background',
        'In Maintenance Mode',
        'Output Path',
        'Append Output',
        'Output Email',
    ];

    /**
     * Builds a table from one or more models.
     *
     * @param $tasks
     */
    protected function generateTable($tasks)
    {
        // Create a collection if the argument is a single model
        if ($tasks instanceof ScheduledTask) {
            $tasks = collect([$tasks]);
        }

        $attributes = $tasks
                        ->map(function ($task) {
                            return [
                               $task->id,
                               $task->type,
                               $task->task,
                               $task->description ?? 'N/A',
                               $task->cron,
                               $task->timezone ?? config('app.timezone'),
                               implode(',', $task->environments) ?: 'None',
                               $task->queue ?? 'N/A',
                               $task->without_overlapping ? 'Yes' : 'No',
                               $task->one_one_server ? 'Yes' : 'No',
                               $task->run_in_background ? 'Yes' : 'No',
                               $task->even_in_maintenance_mode ? 'Yes' : 'No',
                               $task->output_path ?? 'N/A',
                               $task->append_output ? 'Yes' : 'No',
                               $task->output_email ?? 'N/A',
                           ];
                        });

        $this->table($this->headers, $attributes, 'box');
    }
}
