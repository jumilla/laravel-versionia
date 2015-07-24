<?php

use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Console\DatabaseStatusCommand as Command;

class DatabaseStatusCommandTest extends TestCase
{
    use ConsoleCommandTrait;

    /**
     * @test
     */
    public function test_whenMigrationDefinitionNothing()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator();
        $command = new Command();

        // 2. condition
        $migrator->shouldReceive('migrationGroups')->andReturn([]);
        $migrator->shouldReceive('seedNames')->andReturn([]);

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();

        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_migrations()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedMigrationsByDesc',
            'makeLogTable',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->registerMigrations('foo', [
            '1.0' => FooMigration::class,
            '1.1' => FooMigration_NoInheritMigrationClass::class,
        ]);
        $migrator->registerMigrations('bar', [
            '1.0' => 'Bar_1_0',
            '1.1' => 'Bar_1_1',
        ]);
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect([
            'foo' => collect([
                (object) ['version' => '1.0', 'class' => FooMigration::class],
            ]),
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();

        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_seeds()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'makeLogTable',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->registerSeeds([
            'foo' => FooSeeder::class,
            'bar' => 'Bar',
        ]);
        $migrator->setDefaultSeed('bar');

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();

        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_defaultSeedNotFound()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'makeLogTable',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->registerSeeds([
            'foo' => FooSeeder::class,
        ]);
        $migrator->setDefaultSeed('bar');

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();

        $this->runCommand($app, $command, []);
    }
}
