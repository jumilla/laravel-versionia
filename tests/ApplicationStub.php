<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Support\Facades\Facade;

class ApplicationStub extends Container implements ApplicationContract
{
    /**
     * @param array $mocks
     */
    public function __construct(array $mocks = [])
    {
        $this->addMocks($mocks);

        Facade::setFacadeApplication($this);

        $this['path.base'] = __DIR__.'/sandbox';
        $this['path'] = __DIR__.'/sandbox/app';
    }

    /**
     * @param array $mocks
     */
    public function addMocks(array $mocks = [])
    {
        foreach ($mocks as $alias => $class) {
            if (is_string($alias)) {
                $this->instance($alias, Mockery::mock($class));
                $this->alias($alias, $class);
            } else {
                $this->instance($class, Mockery::mock($class));
            }
        }
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function basePath()
    {
        return $this['path.base'];
    }

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     *
     * @return string
     */
    public function environment()
    {
        return array_get($this, 'env', 'testing');
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return false;
    }

    /**
     * Register all of the configured providers.
     */
    public function registerConfiguredProviders()
    {
    }

    /**
     * Register a service provider with the application.
     *
     * @param \Illuminate\Support\ServiceProvider|string $provider
     * @param array                                      $options
     * @param bool                                       $force
     *
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $options = [], $force = false)
    {
        return $provider;
    }

    /**
     * Register a deferred provider and service.
     *
     * @param string $provider
     * @param string $service
     */
    public function registerDeferredProvider($provider, $service = null)
    {
    }

    /**
     * Boot the application's service providers.
     */
    public function boot()
    {
    }

    /**
     * Register a new boot listener.
     *
     * @param mixed $callback
     */
    public function booting($callback)
    {
    }

    /**
     * Register a new "booted" listener.
     *
     * @param mixed $callback
     */
    public function booted($callback)
    {
    }

    /**
     * Get the path to the cached services.json file.
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        return $this->bootstrapPath().'/cache/services.json';
    }

    /**
     * Get the path to the cached packages.php file.
     *
     * @return string
     */
    public function getCachedPackagesPath()
    {
        return $this->bootstrapPath().'/cache/packages.php';
    }

    public function getNamespace()
    {
        return 'App\\';
    }
}
