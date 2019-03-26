<?php

namespace Koomai\Scheduler\Tests;

use Koomai\Scheduler\Constants\TaskType;
use Koomai\Scheduler\SchedulerServiceProvider;
use Orchestra\Testbench\TestCase;

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
    public function shouldDisplayErrorMessageWhenIncorrectArtisanCommandIsEntered()
    {
       $this->artisan('schedule:add')
           ->expectsQuestion('Select type of scheduled task', TaskType::ARTISAN)
           ->expectsQuestion('Enter your artisan command with arguments and options, e.g. `telescope:prune --hours=24`', 'command:invalid')
           ->expectsOutput('`command:invalid` is not a valid  command. Please start again');

       $this->assertEquals(1,1);

       dump('test');
    }
}
