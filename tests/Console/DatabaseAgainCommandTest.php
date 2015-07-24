<?php

use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Console\DatabaseAgainCommand as Command;

class DatabaseAgainCommandTest extends TestCase
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

        // 3. test
        try {
            $this->runCommand($app, $command, []);
        } catch (RuntimeException $ex) {
            Assert::isTrue(true);
        }
    }

    /**
     * @test
     */
    public function test_whenGroupNotDefined()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator();
        $command = new Command();

        // 2. condition
        $migrator->shouldReceive('migrationGroups')->andReturn([]);

        // 3. test
        try {
            $this->runCommand($app, $command, ['group' => 'foo']);

            Assert::isFalse(true);
        } catch (InvalidArgumentException $ex) {
            Assert::isTrue(true);
        }
    }

    /**
     * @test
     */
    public function test_whenGroupDefined_andVersionNotDefined()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
        ]);
        $command = new Command();

        // 2. condition
        $migrator->shouldReceive('migrationGroups')->andReturn([]);
        $migrator->registerMigrations('foo', []);

        // 3. test
        try {
            $this->runCommand($app, $command, ['group' => 'foo']);

            Assert::isFalse(true);
        } catch (InvalidArgumentException $ex) {
            Assert::isTrue(true);
        }
    }

    /**
     * @test
     */
    public function test_whenGroupDefined_andNotInstalled()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedLatestMigrations',
            'makeLogTable',
            'removeMigrationLog',
            'doDowngrade',
            'doUpgrade',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->registerMigrations('foo', [
            '2.0' => 'Foo_2_0',
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->shouldReceive('installedLatestMigrations')->andReturn(collect());

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('removeMigrationLog')->never();
        $migrator->shouldReceive('doDowngrade')->never();
        $migrator->shouldReceive('doUpgrade')->never();

        $this->runCommand($app, $command, ['group' => 'foo']);
    }

    /**
     * @test
     */
    public function test_whenGroupDefined_andInstalled_butIllegalVersion()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedLatestMigrations',
            'makeLogTable',
            'removeMigrationLog',
            'doDowngrade',
            'doUpgrade',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->registerMigrations('foo', [
            '2.0' => 'Foo_2_0',
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->shouldReceive('installedLatestMigrations')->andReturn(collect([
            'foo' => (object) ['version' => '1.1', 'class' => 'Foo_1_1'],
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('removeMigrationLog')->never();
        $migrator->shouldReceive('doDowngrade')->never();
        $migrator->shouldReceive('doUpgrade')->never();

        $this->runCommand($app, $command, ['group' => 'foo']);
    }

    /**
     * @test
     */
    public function test_whenGroupDefined_andInstalled_butOlder()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedLatestMigrations',
            'makeLogTable',
            'removeMigrationLog',
            'doDowngrade',
            'doUpgrade',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->registerMigrations('foo', [
            '3.0' => 'Foo_3_0',
            '2.0' => 'Foo_2_0',
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->shouldReceive('installedLatestMigrations')->andReturn(collect([
            'foo' => (object) ['version' => '1.0', 'class' => 'Foo_1_0'],
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('removeMigrationLog')->never();
        $migrator->shouldReceive('doDowngrade')->never();
        $migrator->shouldReceive('doUpgrade')->never();

        $this->runCommand($app, $command, ['group' => 'foo']);
    }

    /**
     * @test
     */
    public function test_whenGroupDefined_andInstalled()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedLatestMigrations',
            'makeLogTable',
            'removeMigrationLog',
            'doDowngrade',
            'doUpgrade',
        ]);
        $command = new Command();

        // 2. condition
        $migrator->registerMigrations('foo', [
            '2.0' => 'Foo_2_0',
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->shouldReceive('installedLatestMigrations')->andReturn(collect([
            'foo' => (object) ['version' => '2.0', 'class' => 'Foo_2_0'],
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('removeMigrationLog')->once()->with('foo', '2.0');
        $migrator->shouldReceive('doDowngrade')->once()->with('foo', '2.0', 'Foo_2_0');
        $migrator->shouldReceive('doUpgrade')->once()->with('foo', '2.0', 'Foo_2_0');

        $this->runCommand($app, $command, ['group' => 'foo']);
    }

    /**
     * @test
     */
    public function test_whenGroupDefined_andInstalled_withSeedOption()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([
            'installedLatestMigrations',
            'makeLogTable',
            'removeMigrationLog',
            'doDowngrade',
            'doUpgrade',
        ]);
        $command = $this->createMock(Command::class, [
            'call',
        ]);

        // 2. condition
        $migrator->registerMigrations('foo', [
            '2.0' => 'Foo_2_0',
            '1.0' => 'Foo_1_0',
        ]);
        $migrator->shouldReceive('installedLatestMigrations')->andReturn(collect([
            'foo' => (object) ['version' => '2.0', 'class' => 'Foo_2_0'],
        ]));

        // 3. test
        $migrator->shouldReceive('makeLogTable')->once();
        $migrator->shouldReceive('removeMigrationLog')->once()->with('foo', '2.0');
        $migrator->shouldReceive('doDowngrade')->once()->with('foo', '2.0', 'Foo_2_0');
        $migrator->shouldReceive('doUpgrade')->once()->with('foo', '2.0', 'Foo_2_0');
        $command->shouldReceive('call')->with('database:seed', ['name' => 'bar', '--force' => true])->once()->andReturn(0);

        $this->runCommand($app, $command, ['group' => 'foo', '--seed' => 'bar']);
    }

    /**
     * @test
     */
    public function test_inProductionEnvironment_andUserCancel()
    {
        $this->runCommandAndUserCancel([Migrator::class], Command::class, ['group' => 'foo']);
    }
}
