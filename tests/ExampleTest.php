<?php

namespace Koomai\Scheduler\Tests;

use Orchestra\Testbench\TestCase;
use Koomai\Scheduler\ScheduledTask;
use Koomai\Scheduler\SchedulerServiceProvider;

class ExampleTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/factories');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->artisan('migrate', ['--database' => 'testing']);
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
        $app['config']->set('scheduler.table', 'scheduled_tasks');
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
        $task = factory(ScheduledTask::class)->create();

        $this->assertInstanceOf(ScheduledTask::class, $task);
    }
}
