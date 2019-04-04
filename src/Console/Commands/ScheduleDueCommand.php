<?php

namespace Koomai\Scheduler\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\CallbackEvent;
use Koomai\Scheduler\Contracts\ScheduledTaskRepositoryInterface;

class ScheduleDueCommand extends ScheduleCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all scheduled tasks that are due';

    private $headers = [
        'Id',
        'Type',
        'Task',
        'Description',
        'Cron',
        'Next due',
        'Environments',
    ];

    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @param \Koomai\Scheduler\Contracts\ScheduledTaskRepositoryInterface $repository
     */
    public function __construct(Schedule $schedule, ScheduledTaskRepositoryInterface $repository)
    {
        parent::__construct($repository);

        $this->schedule = $schedule;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (empty($this->schedule->events())) {
            $this->warn('No scheduled tasks found');

            return;
        }

        $eventsDue = collect($this->schedule->events())->map(function (Event $event) {
            $scheduledTask = $this->mapEventToScheduledTask($event);
            dump($event->timezone);
            return [
                'id' => $scheduledTask ? $scheduledTask->id : 'N/A',
                'type' => $scheduledTask ? $scheduledTask->type : 'Console Kernel',
                'task' => $scheduledTask ? $scheduledTask->task : $this->parseTaskFromEvent($event),
                'description'   => $scheduledTask ? $scheduledTask->description : $event->description,
                'cron' => $event->expression,
                'due' => $this->getNextRunDate($event),
                'environments' => implode(', ', $event->environments),
            ];
        });

        $this->table($this->headers, $eventsDue);
    }

    /**
     * @param \Illuminate\Console\Scheduling\Event $event
     *
     * @return \Koomai\Scheduler\ScheduledTask|null
     */
    private function mapEventToScheduledTask(Event $event)
    {
        $task = $this->parseTaskFromEvent($event);

        return $this->repository->findByTaskAndCronSchedule($task, $event->expression);
    }

    /**
     * @param \Illuminate\Console\Scheduling\Event $event
     *
     * @return string
     */
    private function parseTaskFromEvent(Event $event): string
    {
        if ($event instanceof CallbackEvent) {
            $task = $event->getSummaryForDisplay();
        } else {
            $task = Str::after($event->command, "'artisan' ");
        }

        return $task;
    }

    /**
     * CronExpression returns all dates with UTC timezone,
     * This method sets it back to the timezone attached to the event
     *
     * @param \Illuminate\Console\Scheduling\Event $event
     *
     * @return string
     */
    private function getNextRunDate(Event $event): string
    {
        $date = $event->nextRunDate();

        if ($event->timezone !== $date->format('e')) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d H:i:s'), $event->timezone)
                          ->setTimezone($event->timezone);
        }

        return $date->format(config('scheduler.date_format'));
    }
}
