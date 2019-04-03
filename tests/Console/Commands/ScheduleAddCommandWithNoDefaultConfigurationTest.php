<?php

namespace Koomai\Scheduler\Tests;

use Koomai\Scheduler\Constants\TaskType;
use Koomai\Scheduler\SchedulerServiceProvider;
use Koomai\Scheduler\Tests\Stubs\TestJob;
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

    /**
     * @test
     */
    public function shouldErrorAndExitIfRequiredOptionsAreMissing()
    {
        $scenarios = [
            'no options' => [],
            'only type' => ['--type' => 'command'],
            'only task' => ['--task' => 'cache:clear --quiet'],
            'only cron' => ['--cron' => '"* * * * *"'],
            'type and task' => ['--type' => 'command', '--task' => 'cache:clear --quiet'],
            'type and cron' => ['--type' => 'command', '--cron' => '"* * * * *"'],
            'task and cron' => ['--task' => 'cache:clear --quiet', '--cron' => '"* * * * *"'],
        ];

        foreach ($scenarios as $scenario) {
            $exitCode = $this->artisan('schedule:add', $scenario);
            $this->assertEquals(1, $exitCode);
        }
    }

    /**
     * @test
     */
    public function shouldErrorAndExitIfTypeIsInvalid()
    {
        $exitCode = $this->artisan(
            'schedule:add',
            [
                '--type' => 'invalid',
                '--task' => 'cache:clear --quiet',
                '--cron' => '* * * * *',
            ]
        );
        $this->assertEquals(1, $exitCode);
    }

    /**
     * @test
     */
    public function shouldErrorAndExitIfCommandIsInvalid()
    {
        $exitCode = $this->artisan(
            'schedule:add',
            [
                '--type' => 'command',
                '--task' => 'invalid:command',
                '--cron' => '* * * * *',
            ]
        );
        $this->assertEquals(1, $exitCode);
    }

    /**
     * @test
     */
    public function shouldErrorAndExitIfJobIsInvalid()
    {
        $exitCode = $this->artisan(
            'schedule:add',
            [
                '--type' => 'job',
                '--task' => 'App\Jobs\DoesNotExist',
                '--cron' => '* * * * *',
            ]
        );
        $this->assertEquals(1, $exitCode);
    }

    /**
     * @test
     */
    public function shouldErrorAndExitIfCronExpressionIsInvalid()
    {
        $exitCode = $this->artisan(
            'schedule:add',
            [
                '--type' => 'command',
                '--task' => 'cache:clear --quiet',
                '--cron' => '* * *',
            ]
        );
        $this->assertEquals(1, $exitCode);
    }

    /**
     * @test
     */
    public function shouldErrorAndExitIfTimezoneIsInvalid()
    {
        $exitCode = $this->artisan(
            'schedule:add',
            [
                '--type' => 'command',
                '--task' => 'cache:clear --quiet',
                '--cron' => '* * * * *',
                '--timezone' => 'Invalid/Timezone',
            ]
        );
        $this->assertEquals(1, $exitCode);
    }

    /**
     * This tests that the required options are set and
     * the optional attributes are set with default values (e.g. false, null)
     * @test
     */
    public function shouldSaveScheduledTaskWithDefaultOptionValues()
    {
        $data = [
            'type' => TaskType::COMMAND,
            'task' => 'cache:clear --quiet',
            'description' => null,
            'cron' => '* * * * *',
            'timezone' => null,
            'environments' => '[]',
            'queue' => null,
            'without_overlapping' => 0,
            'run_in_background' => 0,
            'in_maintenance_mode' => 0,
            'output_path' => null,
            'append_output' => 0,
            'output_email' => null,
        ];

        $exitCode = $this->artisan(
            'schedule:add',
            [
                '--type' => $data['type'],
                '--task' => $data['task'],
                '--cron' => $data['cron'],
            ]
        );

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas(config('scheduler.table'), $data);
    }

    /**
     * This tests that the required options are set and
     * the optional attributes are set with entered values
     * @test
     */
    public function shouldSaveScheduledCommandTaskWithEnteredValues()
    {
        $data = [
            'type' => TaskType::COMMAND,
            'task' => 'cache:clear --quiet',
            'description' => 'Some description',
            'cron' => '* * * * *',
            'timezone' => 'Australia/Sydney',
            'environments' => json_encode(['prod','staging']),
            'queue' => null,
            'without_overlapping' => 1,
            'run_in_background' => 1,
            'in_maintenance_mode' => 1,
            'output_path' => 'var/logs/output.log',
            'append_output' => 1,
            'output_email' => 'test@test.com',
        ];

        $exitCode = $this->artisan(
            'schedule:add',
            [
                '--type' => $data['type'],
                '--task' => $data['task'],
                '--cron' => $data['cron'],
                '--description' => $data['description'],
                '--timezone' => $data['timezone'],
                '--environments' => implode(',', json_decode($data['environments'])), //comma-separated values
                '--without-overlapping' => true,
                '--run-in-background' => true,
                '--in-maintenance-mode' => true,
                '--output-path' => $data['output_path'],
                '--append-output' => true,
                '--output-email' => $data['output_email'],
            ]
        );

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas(config('scheduler.table'), $data);
    }

    /**
     * This tests that the required options are set and
     * the optional attributes are set with entered values
     * @test
     */
    public function shouldSaveScheduledJobTaskWithEnteredValues()
    {
        $data = [
            'type' => TaskType::JOB,
            'task' => TestJob::class,
            'description' => 'Some description',
            'cron' => '* * * * *',
            'timezone' => 'Australia/Sydney',
            'environments' => json_encode(['prod','staging']),
            'queue' => 'test',
            'without_overlapping' => 1,
            'run_in_background' => 1,
            'in_maintenance_mode' => 1,
            'output_path' => 'var/logs/output.log',
            'append_output' => 1,
            'output_email' => 'test@test.com',
        ];

        $exitCode = $this->artisan(
            'schedule:add',
            [
                '--type' => $data['type'],
                '--task' => $data['task'],
                '--cron' => $data['cron'],
                '--description' => $data['description'],
                '--queue' => $data['queue'],
                '--timezone' => $data['timezone'],
                '--environments' => implode(',', json_decode($data['environments'])), //comma-separated values
                '--without-overlapping' => true,
                '--run-in-background' => true,
                '--in-maintenance-mode' => true,
                '--output-path' => $data['output_path'],
                '--append-output' => true,
                '--output-email' => $data['output_email'],
            ]
        );

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas(config('scheduler.table'), $data);
    }
}
