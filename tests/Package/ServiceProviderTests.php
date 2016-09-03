<?php

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Event;
use Jumilla\Versionia\Laravel\ServiceProvider;
use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Console;

class ServiceProviderTests extends TestCase
{
    use MockeryTrait;

    /**
     * @test
     */
    public function test_register()
    {
        // 1. setup
        $app = $this->createApplication();
        $provider = new ServiceProvider($app);

        // 2. test
        $provider->register();

        Assert::isInstanceOf(Migrator::class, $app['database.migrator']);
        Assert::isInstanceOf(Migrator::class, $app[Migrator::class]);
        Assert::isInstanceOf(Console\DatabaseStatusCommand::class, $app['command.database.status']);
        Assert::isInstanceOf(Console\DatabaseUpgradeCommand::class, $app['command.database.upgrade']);
        Assert::isInstanceOf(Console\DatabaseCleanCommand::class, $app['command.database.clean']);
        Assert::isInstanceOf(Console\DatabaseRefreshCommand::class, $app['command.database.refresh']);
        Assert::isInstanceOf(Console\DatabaseRollbackCommand::class, $app['command.database.rollback']);
        Assert::isInstanceOf(Console\DatabaseAgainCommand::class, $app['command.database.again']);
        Assert::isInstanceOf(Console\DatabaseSeedCommand::class, $app['command.database.seed']);
        Assert::isInstanceOf(Console\MigrationMakeCommand::class, $app['command.migration.make']);
        Assert::isInstanceOf(Console\SeederMakeCommand::class, $app['command.seeder.make']);
    }

    protected function createApplication(array $mocks = [])
    {
        $app = parent::createApplication([
            'config' => Config::class,
            'events' => Event::class,
        ]);

        $app['config']->shouldReceive('get')->andReturn('migrations');
        $app['events']->shouldReceive('listen');

        return $app;
    }
}
