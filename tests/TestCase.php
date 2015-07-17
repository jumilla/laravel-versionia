<?php

use Illuminate\Database\DatabaseManager;
use Jumilla\Versionia\Laravel\Migrator;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected function createApplication()
    {
        $this->app = new ApplicationStub([
            'db' => DatabaseManager::class,
        ]);

        return $this->app;
    }

    protected function createMigrator(array $overrides = null)
    {
        $migrator = $this->createMock(Migrator::class, $overrides, function () {
            return [$this->app['db']];
        });

        $this->app->instance('database.migrator', $migrator);
        $this->app->alias('database.migrator', Migrator::class);

        return $migrator;
    }
}
