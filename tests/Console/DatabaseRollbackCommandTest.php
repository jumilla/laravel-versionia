<?php

use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Console\DatabaseRollbackCommand as Command;

class DatabaseRollbackCommandTest extends TestCase
{
    use ConsoleCommandTrait;

    /**
     * @test
     */
    public function test_withNoParameter()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator();
        $command = new Command();

        // 2. condition
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->never();
        $migrator->shouldReceive('doDowngrade')->never();

        try {
            $this->runCommand($app, $command, []);

            Assert::isFalse(true);
        } catch (RuntimeException $ex) {
            Assert::isTrue(true);
        }
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
        $migrator->shouldReceive('migrationGroups')->once()->andReturn([]);
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->never();
        $migrator->shouldReceive('doDowngrade')->never();

        try {
            $this->runCommand($app, $command, ['group' => 'bar']);

            Assert::isFalse(true);
        } catch (InvalidArgumentException $ex) {
            Assert::isTrue(true);
        }
    }

    /**
     * @test
     */
    public function test_whenSpecifiedGroupNotDefined()
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
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->never();
        $migrator->shouldReceive('doDowngrade')->never();

        try {
            $this->runCommand($app, $command, ['group' => 'bar']);
            Assert::isFalse(true);
        } catch (InvalidArgumentException $ex) {
            Assert::isTrue(true);
        }
    }

    /**
     * @test
     */
    public function test_whenNotMigrated()
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
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doDowngrade')->never();

        $this->runCommand($app, $command, ['group' => 'foo']);
    }

    /**
     * @test
     */
    public function test_when1Migrated()
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
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect([
            'foo' => [
                (object) ['version' => '1.0', 'class' => 'Foo_1_0'],
            ],
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doDowngrade')->once();

        $this->runCommand($app, $command, ['group' => 'foo']);
    }

    /**
     * @test
     */
    public function test_when2Migrated()
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
            '2.0' => 'Foo_2_0',
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect([
            'foo' => [
                (object) ['version' => '2.0', 'class' => 'Foo_2_0'],
                (object) ['version' => '1.0', 'class' => 'Foo_1_0'],
            ],
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doDowngrade')->once();

        $this->runCommand($app, $command, ['group' => 'foo']);
    }

    /**
     * @test
     */
    public function test_when2Migrated_withAllOption()
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
            '2.0' => 'Foo_2_0',
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->shouldReceive('installedMigrationsByDesc')->andReturn(collect([
            'foo' => [
                (object) ['version' => '2.0', 'class' => 'Foo_2_0'],
                (object) ['version' => '1.0', 'class' => 'Foo_1_0'],
            ],
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('doDowngrade')->twice();

        $this->runCommand($app, $command, ['group' => 'foo', '--all' => true]);
    }

    /**
     * @test
     */
    public function test_inProductionEnvironment_andUserCancel()
    {
        $this->runCommandAndUserCancel([Migrator::class], Command::class, ['group' => 'foo']);
    }
}
