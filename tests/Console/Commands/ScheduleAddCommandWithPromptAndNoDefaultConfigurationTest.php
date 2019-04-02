<?php

namespace Koomai\Scheduler\Tests;

use Koomai\Scheduler\Constants\TaskType;
use Koomai\Scheduler\SchedulerServiceProvider;
use Orchestra\Testbench\TestCase;

class ScheduleAddCommandWithPromptAndNoDefaultConfigurationTest extends TestCase
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

    protected function getPackageProviders($app)
    {
        return [SchedulerServiceProvider::class];
    }

    protected function mapToChoice(int $choice)
    {
        return $choice === 1 ? 'Yes' : 'No';
    }

    /**
     * @test
     */
    public function shouldDisplayErrorAndExitIfTaskTypeIsInvalid()
    {
        $this->artisan('schedule:add')
            ->expectsQuestion(trans('scheduler::questions.type'), 'Invalid')
            ->expectsOutput(trans('scheduler::messages.invalid_task_type', ['type' => 'Invalid']))
            ->assertExitCode(1);
    }

    /**
     * This tests the whole flow of schedule:add with prompts for an artisan command
     * It also tests that the user is prompted again in cases where input has
     * validation rules and fails.
     *
     * @test
     */
    public function shouldPromptForAllQuestionsAndSaveArtisanCommandTask()
    {
        $data = [
            'type' => TaskType::COMMAND,
            'task' => 'inspire --no-interaction',
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

        $this->artisan('schedule:add')
             ->expectsQuestion(trans('scheduler::questions.type'), $data['type'])
//             ->expectsQuestion(trans('scheduler::questions.task.artisan'), 'invalid:command')
//             ->expectsOutput(trans('scheduler::messages.invalid_artisan_command', ['command' => 'invalid:command']))
             ->expectsQuestion(trans('scheduler::questions.task.artisan'), $data['task']);
//            ->expectsQuestion(trans('scheduler::questions.description'), $data['description'])
//            ->expectsQuestion(trans('scheduler::questions.cron'), '* * *')
//            ->expectsOutput(trans('scheduler::messages.invalid_cron_expression', ['cron' => '* * *']))
//            ->expectsQuestion(trans('scheduler::questions.cron'), '* * * * *')
//            ->expectsQuestion(trans('scheduler::questions.timezone'), 'Invalid/Timezone')
//            ->expectsOutput(trans('scheduler::messages.invalid_timezone', ['timezone' => 'Invalid/Timezone']))
//            ->expectsQuestion(trans('scheduler::questions.timezone'), 'Australia/Sydney')
//            ->expectsQuestion(trans('scheduler::questions.environments'), implode(',', json_decode($data['environments'])))
//            ->expectsQuestion(trans('scheduler::questions.overlapping'), $this->mapToChoice($data['without_overlapping']))
//            ->expectsQuestion(trans('scheduler::questions.one_server'), $this->mapToChoice($data['on_one_server']))
//            ->expectsOutput(trans('scheduler::messages.cache_driver_alert'))
//            ->expectsQuestion(trans('scheduler::questions.maintenance'), $this->mapToChoice($data['in_maintenance_mode']))
//            ->expectsQuestion(trans('scheduler::questions.background'), $this->mapToChoice($data['run_in_background']))
//            ->expectsQuestion(trans('scheduler::questions.confirm_output_path'), true)
//            ->expectsQuestion(trans('scheduler::questions.output_path'), $data['output_path'])
//            ->expectsQuestion(trans('scheduler::questions.append_output'), $this->mapToChoice($data['append_output']))
//            ->expectsQuestion(trans('scheduler::questions.output_email'), $data['output_email'])
//            ->assertExitCode(0);

            $this->assertDatabaseHas(config('scheduler.table'), $data);
    }
}
