<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Jumilla\Versionia\Laravel\Migrator;

class MigrationTests extends TestCase
{
    use MockeryTrait;

    /**
     * @test
     */
    public function test_compareMethod()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = new Migrator($app['db'], $app['config']);

        // 2. condition

        // 3.1. test
        Assert::equals(0, $migrator->compareMigrationVersion('1.0', '1.0'));
        Assert::lessThan(0, $migrator->compareMigrationVersion('0.9', '1.0'));
        Assert::greaterThan(0, $migrator->compareMigrationVersion('1.1', '1.0'));

        // 3.2. test
        Assert::equals(0, $migrator->compareMigrationVersion('1.0.0', '1.0.0'));
        Assert::lessThan(0, $migrator->compareMigrationVersion('0.9.1', '1.0'));
        Assert::greaterThan(0, $migrator->compareMigrationVersion('1.0.0', '1.0'));
        Assert::greaterThan(0, $migrator->compareMigrationVersion('1.1.1', '1.0'));

        // 3.3. test
        Assert::equals(0, $migrator->compareMigrationVersion(null, null));
        Assert::lessThan(0, $migrator->compareMigrationVersion(null, '1.0'));
        Assert::greaterThan(0, $migrator->compareMigrationVersion('1.0', null));
    }

    /**
     * @test
     */
    public function test_migrationMethods()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = new Migrator($app['db'], $app['config']);

        // 2. condition

        // 3.1. test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerMigration('foo', '1.0', 'Foo_1_0');
        $migrator->registerMigration('foo', '1.1', 'Foo_1_1');
        Assert::containsAll(['foo'], $migrator->migrationGroups());
        Assert::same(['1.0', '1.1'], array_keys($migrator->migrationVersions('foo')));
        Assert::same(['1.0', '1.1'], array_keys($migrator->migrationVersions('foo', false)));
        Assert::same(['1.1', '1.0'], array_keys($migrator->migrationVersions('foo', true)));
        Assert::same(['1.1', '1.0'], array_keys($migrator->migrationVersionsByDesc('foo')));
        Assert::same([], $migrator->migrationVersions('bar'));

        // 3.2. test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerMigrations('foo', [
            '1.0' => 'Foo_1_0',
            '1.1' => 'Foo_1_1',
        ]);
        Assert::same(['1.0', '1.1'], array_keys($migrator->migrationVersions('foo')));
        Assert::same(['1.0', '1.1'], array_keys($migrator->migrationVersions('foo', false)));
        Assert::same(['1.1', '1.0'], array_keys($migrator->migrationVersions('foo', true)));
        Assert::same(['1.1', '1.0'], array_keys($migrator->migrationVersionsByDesc('foo')));
        Assert::same([], $migrator->migrationVersions('bar'));

        // 3.3. test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerMigrations('foo', [
            '1.1' => 'Foo_1_1',
            '1.0' => 'Foo_1_0',
        ]);
        Assert::same(['1.0', '1.1'], array_keys($migrator->migrationVersions('foo')));
        Assert::same(['1.0', '1.1'], array_keys($migrator->migrationVersions('foo', false)));
        Assert::same(['1.1', '1.0'], array_keys($migrator->migrationVersions('foo', true)));
        Assert::same(['1.1', '1.0'], array_keys($migrator->migrationVersionsByDesc('foo')));
        Assert::same([], $migrator->migrationVersions('bar'));

        // 3.4. test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerMigration('foo', '1.0', 'Foo_1_0');
        $migrator->registerMigration('bar', '1.0', 'Bar_1_0');
        Assert::containsAll(['foo', 'bar'], $migrator->migrationGroups());
    }

    /**
     * @test
     */
    public function test_migrationLatestVersionMethod()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = new Migrator($app['db'], $app['config']);

        // 2. condition

        // 3.1. test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerMigrations('foo', [
            '1.0' => 'Foo_1_0',
            '1.1' => 'Foo_1_1',
        ]);
        Assert::same('1.1', $migrator->migrationLatestVersion('foo'));

        // 3.2. test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerMigrations('foo', [
            '1.1' => 'Foo_1_1',
            '1.0' => 'Foo_1_0',
        ]);
        Assert::same('1.1', $migrator->migrationLatestVersion('foo'));
        Assert::same(Migrator::VERSION_NULL, $migrator->migrationLatestVersion('bar'));
    }

    /**
     * @test
     */
    public function test_migrationClassMethod()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = new Migrator($app['db'], $app['config']);

        // 2. condition

        // 3.1. test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerMigrations('foo', [
            '1.0' => 'Foo_1_0',
        ]);
        Assert::same('Foo_1_0', $migrator->migrationClass('foo', '1.0'));
        Assert::isNull($migrator->migrationClass('foo', '2.0'));
    }

    /**
     * @test
     */
    public function test_seedMethods()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = new Migrator($app['db'], $app['config']);

        // 2. condition

        // 3.1. test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerSeed('foo', 'Foo');
        Assert::containsAll(['foo'], $migrator->seedNames());
        Assert::same('Foo', $migrator->seedClass('foo'));

        // 3.2. test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerSeeds([
            'foo' => 'Foo',
            'bar' => 'Bar',
        ]);
        $migrator->setDefaultSeed('foo');
        Assert::containsAll(['foo', 'bar'], $migrator->seedNames());
        Assert::same('Foo', $migrator->seedClass('foo'));
        Assert::same('Bar', $migrator->seedClass('bar'));
        Assert::same('foo', $migrator->defaultSeed());

        // 3.3. [Mistake] test
        $migrator = new Migrator($app['db'], $app['config']);
        $migrator->registerSeeds([
            'foo', 'Foo',
            'bar', 'Bar',
        ]);
        Assert::containsAll(['foo', 'Foo', 'bar', 'Bar'], $migrator->seedNames());
        Assert::isNull($migrator->seedClass('foo'));
        Assert::same('foo', $migrator->seedClass(0));
    }

    /**
     * @test
     */
    public function test_makeLogTableMethod_whenTableNotExist()
    {
        // 1. setup
        $app = $this->createApplication();
        $schemaFacade = Mockery::mock('alias:'.SchemaFacade::class);
        $migrator = new Migrator($app['db'], $app['config']);

        // 2. condition
        $schemaFacade->shouldReceive('hasTable')->andReturn(false);

        // 3. test
        $schemaFacade->shouldReceive('create')->once()->andReturnUsing(function ($table, Closure $closure) {
            $blueprint = new Blueprint($table);
            $closure($blueprint);

            $columns = array_pluck($blueprint->getAddedColumns(), 'name');
            Assert::contains('group', $columns);
            Assert::contains('version', $columns);
            Assert::contains('class', $columns);
        });

        $migrator->makeLogTable();
    }

    /**
     * @test
     */
    public function test_makeLogTableMethod_whenTableExist()
    {
        // 1. setup
        $app = $this->createApplication();
        $schemaFacade = Mockery::mock('alias:'.SchemaFacade::class);
        $migrator = new Migrator($app['db'], $app['config']);

        // 2. condition
        $schemaFacade->shouldReceive('hasTable')->andReturn(true);

        // 3. test
        $schemaFacade->shouldReceive('create')->never();

        $migrator->makeLogTable();
    }

    /**
     * @test
     */
    public function test_installedMigrationsMethods()
    {
        // 1. setup
        $app = $this->createApplication();
        $db = $app['db'];
        $migrator = new Migrator($db, $app['config']);

        // 2. condition
        $db->shouldReceive('table')->with('_migrations')->andReturn($db);
        $db->shouldReceive('get')->andReturn([
            (object) ['group' => 'foo', 'version' => '1.2'],
            (object) ['group' => 'foo', 'version' => '1.0'],
            (object) ['group' => 'foo', 'version' => '1.1'],
            (object) ['group' => 'bar', 'version' => '1.0'],
        ]);

        // 3. test
        $items = $migrator->installedMigrations();
        Assert::containsAll(['foo', 'bar'], $items->keys()->all());
        Assert::same(['1.0', '1.1', '1.2'], $items->get('foo')->pluck('version')->all());

        $items = $migrator->installedMigrations(false);
        Assert::containsAll(['foo', 'bar'], $items->keys()->all());
        Assert::same(['1.0', '1.1', '1.2'], $items->get('foo')->pluck('version')->all());

        $items = $migrator->installedMigrations(true);
        Assert::containsAll(['foo', 'bar'], $items->keys()->all());
        Assert::same(['1.2', '1.1', '1.0'], $items->get('foo')->pluck('version')->all());

        $items = $migrator->installedMigrationsByDesc();
        Assert::containsAll(['foo', 'bar'], $items->keys()->all());
        Assert::same(['1.2', '1.1', '1.0'], $items->get('foo')->pluck('version')->all());
    }

    /**
     * @test
     */
    public function test_installedLatestMigrationsMethod()
    {
        // 1. setup
        $app = $this->createApplication();
        $db = $app['db'];
        $migrator = new Migrator($db, $app['config']);

        // 2. condition
        $db->shouldReceive('table')->with('_migrations')->andReturn($db);
        $db->shouldReceive('get')->andReturn([
            (object) ['group' => 'foo', 'version' => '1.1'],
            (object) ['group' => 'foo', 'version' => '1.2'],
            (object) ['group' => 'foo', 'version' => '1.0'],
            (object) ['group' => 'bar', 'version' => '1.0'],
        ]);

        // 3. test
        $items = $migrator->installedLatestMigrations();
        Assert::containsAll(['foo', 'bar'], $items->keys()->all());
        Assert::same('1.2', data_get($items, 'foo.version'));
        Assert::same('1.0', data_get($items, 'bar.version'));
        Assert::same(null, data_get($items, 'baz.version'));
    }

    /**
     * @test
     */
    public function test_addMigrationLogMethod()
    {
        // 1. setup
        $app = $this->createApplication();
        $db = $app['db'];
        $migrator = new Migrator($db, $app['config']);

        // 2. condition
        $db->shouldReceive('table')->with('_migrations')->andReturn($db);

        // 3. test
        $db->shouldReceive('insert')->once()->andReturnUsing(function (array $data) {
            Assert::same('foo', $data['group']);
            Assert::same('1.0', $data['version']);
            Assert::same('Foo_1_0', $data['class']);
        });

        $migrator->addMigrationLog('foo', '1.0', 'Foo_1_0');
    }

    /**
     * @test
     */
    public function test_removeMigrationLogMethod()
    {
        // 1. setup
        $app = $this->createApplication();
        $db = $app['db'];
        $migrator = new Migrator($db, $app['config']);

        // 2. condition
        $db->shouldReceive('table')->with('_migrations')->andReturn($db);

        // 3. test
        $db->shouldReceive('where')->with('group', 'foo')->once()->andReturn($db);
        $db->shouldReceive('where')->with('version', '1.0')->once()->andReturn($db);
        $db->shouldReceive('delete')->once();

        $migrator->removeMigrationLog('foo', '1.0');
    }

    /**
     * @test
     */
    public function test_doUpgradeMethod()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator(['addMigrationLog']);

        // 2. condition

        // 3. test
        $migrator->shouldReceive('addMigrationLog')->with('foo', '1.0', FooMigration::class)->once();

        $migrator->doUpgrade('foo', '1.0', FooMigration::class);
    }

    /**
     * @test
     */
    public function test_doDowngradeMethod()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator(['removeMigrationLog']);

        // 2. condition

        // 3. test
        $migrator->shouldReceive('removeMigrationLog')->with('foo', '1.0')->once();

        $migrator->doDowngrade('foo', '1.0', FooMigration::class);
    }

    /**
     * @test
     */
    public function test_doDowngradeMethod_useDefinition()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator(['removeMigrationLog']);

        // 2. condition
        $migrator->registerMigration('foo', '1.0', FooMigration::class);

        // 3. test
        $migrator->shouldReceive('removeMigrationLog')->with('foo', '1.0')->once();

        $migrator->doDowngrade('foo', '1.0');
    }
}
