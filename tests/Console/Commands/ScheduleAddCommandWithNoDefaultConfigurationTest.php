<?php

namespace Koomai\Scheduler\Tests;

use Koomai\Scheduler\Constants\TaskType;
use Koomai\Scheduler\SchedulerServiceProvider;
use Orchestra\Testbench\TestCase;

class ScheduleAddCommandWithNoDefaultConfigurationTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/factories');
        $this->artisan('migrate', ['--database' => 'testing']);
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
    public function shouldDisplayErrorAndExitIfRequiredOptionsAreMissing()
    {
        $scenarios = [
            'no options' => [],
            'only type' => ['--type' => 'command'],
            'only task' => ['--task' => 'inspire'],
            'only cron' => ['--cron' => '"* * * * *"'],
            'type and task' => ['--type' => 'command', '--task' => 'inspire'],
            'type and cron' => ['--type' => 'command', '--cron' => '"* * * * *"'],
            'task and cron' => ['--task' => 'inspire', '--cron' => '"* * * * *"'],
        ];

        foreach ($scenarios as $scenario) {
            $exitCode = $this->artisan('schedule:add', $scenario);
            $this->assertEquals(1, $exitCode);
        }
    }

    /**
     * This tests the whole flow of schedule:add with prompts for an artisan command
     * It also tests that the user is prompted again in cases where input has
     * validation rules and fails.
     *
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

        $this->assertDatabaseHas(config('scheduler.table'), $data);
    }
}
