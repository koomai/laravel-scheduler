<?php

namespace Koomai\Scheduler\Console\Commands;

class ScheduleDeleteCommand extends ScheduleCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:delete {id : Scheduled task Id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a scheduled task by Id';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');
        $task = $this->repository->delete($id);

        if ($task) {
            $this->info("Scheduled task [{$id}] has been deleted");

            return;
        }

        $this->warn("Scheduled task [{$id}] does not exist");
    }
}
