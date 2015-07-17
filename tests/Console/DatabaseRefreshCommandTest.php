<?php

use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Console\DatabaseRefreshCommand as Command;

class DatabaseRefreshCommandTest extends TestCase
{
    use ConsoleCommandTrait;

    /**
     * @test
     */
    public function test_whenNoDefinition()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator();
        $command = new Command();

        // 2. condition
        $migrator->shouldReceive('migrationGroups')->andReturn([]);
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();

        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_withSeedOption()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator();
        $command = $this->createMock(Command::class, [
            'call',
        ]);

        // 2. condition
        $migrator->shouldReceive('migrationGroups')->andReturn([]);
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $command->shouldReceive('call')->with('database:seed', ['name' => 'foo'])->once()->andReturn(0);

        $this->runCommand($app, $command, ['--seed' => 'foo']);
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
        $migrator->shouldReceive('migrationGroups')->andReturn([]);
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();

        $this->runCommand($app, $command, ['--force' => '']);
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
            'doUpgrade',
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
            ],
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doDowngrade')->times(2);
        $migrator->shouldReceive('doUpgrade')->times(3);

        $this->runCommand($app, $command, ['--force' => '']);
    }

    /**
     * @test
     */
    public function test_inProductionEnvironment_andUserCancel()
    {
        $this->runCommandAndUserCancel([Migrator::class], Command::class, []);
    }
}
