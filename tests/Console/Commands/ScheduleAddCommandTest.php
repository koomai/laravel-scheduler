<?php

namespace Koomai\Scheduler\Tests;

use Illuminate\Console\Command;
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
//         $app['config']->set('scheduler.environments', ['production']);
    }

    protected function getPackageProviders($app)
    {
        return [SchedulerServiceProvider::class];
    }

    /**
     * @test
     */
    public function shouldDisplayErrorMessageAndExitWhenIncorrectArtisanCommandIsEntered()
    {
       $this->artisan('schedule:add')
           ->expectsQuestion(trans('scheduler::questions.type'), TaskType::COMMAND)
           ->expectsQuestion(trans('scheduler::questions.task.artisan'), 'invalid:command')
           ->expectsOutput(trans('scheduler::messages.invalid_artisan_command', ['task' => 'invalid:command']))
           ->assertExitCode(1);
    }

    /**
     * @test
     */
    public function shouldDisplayErrorMessageAndExitWhenNonExistentJobClassIsEntered()
    {
        $this->artisan('schedule:add')
             ->expectsQuestion(trans('scheduler::questions.type'), TaskType::JOB)
             ->expectsQuestion(trans('scheduler::questions.task.job'), 'App\Job\DoesNotExist')
             ->expectsOutput(trans('scheduler::messages.invalid_job_class', ['job' => 'App\Job\DoesNotExist']))
             ->assertExitCode(1);
    }

    /**
     * @test
     */
    public function shouldDisplayErrorMessageIfCronExpressionIsWrongTwoTimes()
    {
        $this->artisan('schedule:add')
             ->expectsQuestion(trans('scheduler::questions.type'), TaskType::COMMAND)
             ->expectsQuestion(trans('scheduler::questions.task.artisan'), 'schedule:show')
             ->expectsQuestion(trans('scheduler::questions.description'), 'Some description')
             ->expectsQuestion(trans('scheduler::questions.cron'), '* * *')
             ->expectsOutput(trans('scheduler::messages.invalid_cron_warn', ['cron' => '* * *']))
             ->expectsQuestion(trans('scheduler::questions.cron'), '* * * *')
             ->expectsOutput(trans('scheduler::messages.invalid_cron_error', ['cron' => '* * * *']))
             ->assertExitCode(1);
    }

    /**
     * @test
     */
    public function shouldNotAskForEnvironmentsIfAlreadyDefinedInConfig()
    {

        $this->artisan('schedule:add')
            ->expectsQuestion(trans('scheduler::questions.type'), TaskType::COMMAND)
            ->expectsQuestion(trans('scheduler::questions.task.artisan'), 'schedule:show')
            ->expectsQuestion(trans('scheduler::questions.description'), 'Some description')
            ->expectsQuestion(trans('scheduler::questions.cron'), '* * * * *')
            ->expectsQuestion(trans('scheduler::questions.timezone'), 'Australia/Sydney')
            ->expectsQuestion(trans('scheduler::questions.environments'), 'prod,staging')
            ->expectsQuestion(trans('scheduler::questions.overlapping'), 'Yes')
            ->expectsQuestion(trans('scheduler::questions.one_server'), 'Yes')
            ->expectsOutput(trans('scheduler::messages.cache_driver_alert'))
            ->expectsQuestion(trans('scheduler::questions.maintenance'), 'Yes')
            ->expectsQuestion(trans('scheduler::questions.background'), 'Yes')
            ->expectsQuestion(trans('scheduler::questions.confirm_output_path'), true)
            ->expectsQuestion(trans('scheduler::questions.output_path'), '/var/logs/output.log')
            ->expectsQuestion(trans('scheduler::questions.append_output'), 'Yes')
            ->expectsQuestion(trans('scheduler::questions.output_email'), 'test@test.com')
            ->assertExitCode(0);

            $expectedData = [
                'type' => TaskType::COMMAND,
                'task' => 'schedule:show',
                'description' => 'Some description',
                'cron' => '* * * * *',
                'timezone' => 'Australia/Sydney',
                'environments' => json_encode(['prod','staging']),
                'queue' => null,
                'without_overlapping' => 1,
                'on_one_server' => 1,
                'run_in_background' => 1,
                'in_maintenance_mode' => 1,
                'output_path' => '/var/logs/output.log',
                'append_output' => 1,
                'output_email' => 'test@test.com',
            ];

            $this->assertDatabaseHas(config('scheduler.table'), $expectedData);
    }
}
