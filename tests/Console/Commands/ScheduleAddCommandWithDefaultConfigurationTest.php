<?php

namespace Koomai\Scheduler\Tests;

use Koomai\Scheduler\Constants\TaskType;
use Koomai\Scheduler\SchedulerServiceProvider;
use Koomai\Scheduler\Tests\Stubs\TestJob;
use Orchestra\Testbench\TestCase;

class ScheduleAddCommandWithDefaultConfigurationTest extends TestCase
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
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('scheduler.environments', ['prod', 'staging']);
        $app['config']->set('scheduler.without_overlapping', true);
        $app['config']->set('scheduler.run_in_background', true);
        $app['config']->set('scheduler.in_maintenance_mode', true);
        $app['config']->set('scheduler.output_path', 'var/logs/output.log');
        $app['config']->set('scheduler.append_output', true);
        $app['config']->set('scheduler.output_email', 'test@test.com');
    }

    /**
     * This tests that the required options are set and
     * the optional attributes are set from configuration values
     * @test
     */
    public function shouldSaveScheduledCommandTaskWithConfigurationValues()
    {
        $data = [
            'type' => TaskType::COMMAND,
            'task' => 'cache:clear --quiet',
            'description' => null,
            'cron' => '* * * * *',
            'timezone' => null,
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
            ]
        );

        $this->assertEquals(0, $exitCode);

        $this->assertDatabaseHas(config('scheduler.table'), $data);
    }
}
