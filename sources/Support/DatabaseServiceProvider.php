<?php

namespace Jumilla\Versionia\Laravel\Support;

use Illuminate\Support\ServiceProvider;
use Jumilla\Versionia\Laravel\Migrator;

abstract class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
    }

    /**
     * @param string $group
     * @param string $version
     * @param string $class
     */
    protected function migration($group, $version, $class)
    {
        $this->app['database.migrator']->registerMigration($group, $version, $class);
    }

    /**
     * @param string $group
     * @param array  $versions
     */
    protected function migrations($group, array $versions)
    {
        $this->app['database.migrator']->registerMigrations($group, $versions);
    }

    /**
     * @param string $name
     * @param string $class
     * @param bool   $is_default
     */
    protected function seed($name, $class, $is_default = false)
    {
        $this->app['database.migrator']->registerSeed($name, $class);

        if ($is_default) {
            $this->app['database.migrator']->setDefaultSeed($name);
        }
    }

    /**
     * @param array       $seeds
     * @param bool|string $default
     */
    protected function seeds(array $seeds, $default = null)
    {
        $this->app['database.migrator']->registerSeeds($seeds);

        if ($default === true) {
            $default = array_keys($seeds)[0];
        }

        $this->app['database.migrator']->setDefaultSeed($default);
    }
}
