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
           ->expectsQuestion(trans('scheduler::questions.type'), TaskType::ARTISAN)
           ->expectsQuestion(trans('scheduler::questions.task.artisan'), 'command:invalid')
           ->expectsOutput(trans('scheduler::messages.invalid_artisan_command', ['task' => 'command:invalid']))
           ->assertExitCode(1);

       $this->assertEquals(1,1);
    }
}
