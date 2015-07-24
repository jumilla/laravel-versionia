<?php

use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Console\DatabaseSeedCommand as Command;

class DatabaseSeedCommandTest extends TestCase
{
    use ConsoleCommandTrait;

    /**
     * @test
     */
    public function test_whenSeedNotDefined()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator();
        $command = new Command();

        // 2. condition

        // 3. test
        $migrator->shouldReceive('defaultSeed')->once()->andReturn('');
        $migrator->shouldReceive('seedClass')->never();
        $migrator->shouldReceive('installedMigrationsByDesc')->never();

        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_whenSeedRegistered()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([]);
        $command = new Command();

        // 2. condition
        $migrator->registerSeeds([
            'foo' => 'FooSeeder',
        ]);
        $migrator->setDefaultSeed('foo');

        // 3. test
        $this->runCommand($app, $command, []);
    }

    /**
     * @test
     */
    public function test_inProductionEnvironment_withForceOption()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([]);
        $command = new Command();

        // 2. condition
        $migrator->registerSeeds([
            'foo' => 'FooSeeder',
        ]);
        $migrator->setDefaultSeed('foo');

        // 3. test
        $this->runCommand($app, $command, ['--force' => true]);
    }

    /**
     * @test
     */
    public function test_seedRegistered_andSeedSpecified()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([]);
        $command = new Command();

        // 2. condition
        $migrator->registerSeeds([
            'foo' => 'FooSeeder',
        ]);

        // 3. test
        $this->runCommand($app, $command, ['name' => 'foo']);
    }

    /**
     * @test
     */
    public function test_seedNotFound()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([]);
        $command = new Command();

        // 2. condition
        $migrator->registerSeeds([
            'foo' => 'FooSeeder',
        ]);

        // 3. test
        $this->runCommand($app, $command, ['name' => 'bar']);
    }

    /**
     * @test
     */
    public function test_defaultSeedNotFound()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([]);
        $command = new Command();

        // 2. condition
        $migrator->registerSeeds([
            'foo' => 'FooSeeder',
        ]);
        $migrator->setDefaultSeed('bar');

        // 3. test
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
