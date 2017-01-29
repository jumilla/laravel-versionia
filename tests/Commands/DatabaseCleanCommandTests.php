<?php

use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Commands\DatabaseCleanCommand as Command;

class DatabaseCleanCommandTests extends TestCase
{
    use ConsoleCommandTrait;

    /**
     * @test
     */
    public function test_whenMigrationNotInstalled()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator();
        $command = new Command();

        // 2. condition
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doDowngrade')->never();

        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_inProductionEnvironment_withForceOption()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator();
        $command = new Command();

        // 2. condition
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doDowngrade')->never();

        $this->runCommand($app, $command, ['--force' => true]);
    }

    /**
     * @test
     */
    public function test_whenMigrationInstalled()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedMigrationsByDesc',
            'makeLogTable',
            'doDowngrade',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->registerMigrations('foo', [
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->registerMigrations('bar', [
            '1.0' => 'Bar_1_0',
            '1.1' => 'Bar_1_1',
        ]);
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect([
            'foo' => [
                (object) ['version' => '1.0', 'class' => 'Foo_1_0'],
            ],
            'bar' => [
                (object) ['version' => '1.0', 'class' => 'Bar_1_0'],
                (object) ['version' => '1.1', 'class' => 'Bar_1_1'],
            ],
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doDowngrade')->times(3);

        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_inProductionEnvironment_andUserCancel()
    {
        $this->runCommandAndUserCancel([Migrator::class], Command::class, []);
    }
}
