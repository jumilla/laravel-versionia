<?php

use Illuminate\Database\DatabaseManager;
use Jumilla\Versionia\Laravel\Migrator;

class DatabaseServiceProviderTest extends TestCase
{
    use MockeryTrait;

    /**
     * @test
     */
    public function test_migrationMethods_1()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([]);
        $provider = new DatabaseServiceProviderStub($app);

        // 2. test
        $provider->register();
        $provider->migration('foo', '1.0', 'Foo_1_0');

        Assert::contains('foo', $migrator->migrationGroups());
        Assert::notContains('bar', $migrator->migrationGroups());
    }

    /**
     * @test
     */
    public function test_migrationMethods_2()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([]);
        $provider = new DatabaseServiceProviderStub($app);

        // 2. test
        $provider->migration('foo', '1.0', 'Foo_1_0');
        $provider->migrations('bar', [
            '1.0', 'Bar_1_0',
            '1.1', 'Bar_1_1',
        ]);
        Assert::contains('foo', $migrator->migrationGroups());
        Assert::contains('bar', $migrator->migrationGroups());

        $provider->migration('foo', '1.1', 'Foo_1_1');
        Assert::contains('Foo_1_0', $migrator->migrationVersions('foo'));
        Assert::contains('Foo_1_1', $migrator->migrationVersions('foo'));
        Assert::contains('Bar_1_0', $migrator->migrationVersions('bar'));
        Assert::contains('Bar_1_1', $migrator->migrationVersions('bar'));
    }

    /**
     * @test
     */
    public function test_seedMethod()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator([]);
        $provider = new DatabaseServiceProviderStub($app);

        // 2. test
        $provider->seed('foo', 'Foo');
        Assert::contains('foo', $migrator->seedNames());
        Assert::same('Foo', $migrator->seedClass('foo'));
        Assert::isNull($migrator->defaultSeed());

        $provider->seed('foo', 'Foo', true);
        Assert::contains('foo', $migrator->seedNames());
        Assert::same('Foo', $migrator->seedClass('foo'));
        Assert::same('foo', $migrator->defaultSeed());

        $provider->seed('bar', 'Bar');
        Assert::contains('foo', $migrator->seedNames());
        Assert::contains('bar', $migrator->seedNames());
        Assert::same('Bar', $migrator->seedClass('bar'));
        Assert::same('foo', $migrator->defaultSeed());

        $provider->seeds([
            'foo' => 'Foo',
        ]);
        Assert::contains('foo', $migrator->seedNames());
        Assert::same('Foo', $migrator->seedClass('foo'));
        Assert::isNull($migrator->defaultSeed());

        $provider->seeds([
            'foo' => 'Foo',
            'bar' => 'Bar',
        ], true);
        Assert::contains('foo', $migrator->seedNames());
        Assert::same('Foo', $migrator->seedClass('foo'));
        Assert::same('foo', $migrator->defaultSeed());

        $provider->seeds([
            'foo' => 'Foo',
            'bar' => 'Bar',
        ], 'bar');
        Assert::contains('foo', $migrator->seedNames());
        Assert::same('Foo', $migrator->seedClass('foo'));
        Assert::same('bar', $migrator->defaultSeed());
    }
}
