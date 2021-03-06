<?php

namespace Jumilla\Versionia\Laravel;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('database.migrator', function ($app) {
            return new Migrator($app['db'], $app['config']);
        });
        $this->app->alias('database.migrator', Migrator::class);

        $this->registerCommands();
    }

    /**
     * Register the cache related console commands.
     */
    public function registerCommands()
    {
        $this->app->singleton('command.database.status', function ($app) {
            return new Commands\DatabaseStatusCommand();
        });

        $this->app->singleton('command.database.upgrade', function ($app) {
            return new Commands\DatabaseUpgradeCommand();
        });

        $this->app->singleton('command.database.clean', function ($app) {
            return new Commands\DatabaseCleanCommand();
        });

        $this->app->singleton('command.database.refresh', function ($app) {
            return new Commands\DatabaseRefreshCommand();
        });

        $this->app->singleton('command.database.rollback', function ($app) {
            return new Commands\DatabaseRollbackCommand();
        });

        $this->app->singleton('command.database.again', function ($app) {
            return new Commands\DatabaseAgainCommand();
        });

        $this->app->singleton('command.database.seed', function ($app) {
            return new Commands\DatabaseSeedCommand();
        });

        $this->app->singleton('command.migration.make', function ($app) {
            return new Commands\MigrationMakeCommand();
        });

        $this->app->singleton('command.seeder.make', function ($app) {
            return new Commands\SeederMakeCommand();
        });

        $this->commands([
            'command.database.status',
            'command.database.upgrade',
            'command.database.clean',
            'command.database.refresh',
            'command.database.rollback',
            'command.database.again',
            'command.database.seed',
            'command.migration.make',
            'command.seeder.make',
        ]);
    }
}
