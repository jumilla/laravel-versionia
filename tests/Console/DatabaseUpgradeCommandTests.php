<?php

use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Console\DatabaseUpgradeCommand as Command;

class DatabaseUpgradeCommandTests extends TestCase
{
    use ConsoleCommandTrait;

    /**
     * @test
     */
    public function test_whenMigrationNotDefined()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedLatestMigrations',
            'makeLogTable',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->shouldReceive('installedLatestMigrations')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();

        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_inProductionEnvironment_withForceOption()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedLatestMigrations',
            'makeLogTable',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->shouldReceive('installedLatestMigrations')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();

        $this->runCommand($app, $command, ['--force' => true]);
    }

    /**
     * @test
     */
    public function test_whenMigrationDefined()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedLatestMigrations',
            'makeLogTable',
            'doUpgrade',
        ]);
        $command = $this->createMock(Command::class, [
            'call',
        ]);

        // 2. condition
        $migrator->registerMigrations('foo', [
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->shouldReceive('installedLatestMigrations')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doUpgrade')->once();
        $command->shouldReceive('call')->with('database:seed', Mockery::any())->never();

        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_whenMigrationDefined_withSeed()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedLatestMigrations',
            'makeLogTable',
            'doUpgrade',
        ]);
        $command = $this->createMock(Command::class, [
            'call',
        ]);

        // 2. condition
        $migrator->registerMigrations('foo', [
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->shouldReceive('installedLatestMigrations')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doUpgrade')->once();
        $command->shouldReceive('call')->with('database:seed', ['name' => 'foo', '--force' => true])->once()->andReturn(0);

        $this->runCommand($app, $command, ['--seed' => 'foo']);
    }

    /**
     * @test
     */
    public function test_inProductionEnvironment_andUserCancel()
    {
        $this->runCommandAndUserCancel([Migrator::class], Command::class, []);
    }
}
