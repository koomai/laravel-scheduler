<?php

namespace Koomai\Scheduler\Tests;

use Koomai\Scheduler\Constants\TaskType;
use Orchestra\Testbench\TestCase;
use Koomai\Scheduler\ScheduledTask;
use Koomai\Scheduler\SchedulerServiceProvider;

class ScheduleAddCommandTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/factories');
        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup config for table name
        // $app['config']->set('scheduler.table', 'scheduled_tasks');
    }

    protected function getPackageProviders($app)
    {
        return [SchedulerServiceProvider::class];
    }

    /**
     * @test
     */
    public function this_is_an_example_test()
    {
       $this->artisan('schedule:add')
           ->expectsQuestion('Select type of scheduled task', TaskType::ARTISAN)
           ->expectsQuestion('Enter your artisan command with arguments and options, e.g. `telescope:prune --hours=24`', 'cache:clear');
    }
}
