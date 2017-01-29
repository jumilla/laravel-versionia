<?php

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Events\Event;
use Jumilla\Versionia\Laravel\ServiceProvider;
use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Commands;

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
        Assert::isInstanceOf(Commands\DatabaseStatusCommand::class, $app['command.database.status']);
        Assert::isInstanceOf(Commands\DatabaseUpgradeCommand::class, $app['command.database.upgrade']);
        Assert::isInstanceOf(Commands\DatabaseCleanCommand::class, $app['command.database.clean']);
        Assert::isInstanceOf(Commands\DatabaseRefreshCommand::class, $app['command.database.refresh']);
        Assert::isInstanceOf(Commands\DatabaseRollbackCommand::class, $app['command.database.rollback']);
        Assert::isInstanceOf(Commands\DatabaseAgainCommand::class, $app['command.database.again']);
        Assert::isInstanceOf(Commands\DatabaseSeedCommand::class, $app['command.database.seed']);
        Assert::isInstanceOf(Commands\MigrationMakeCommand::class, $app['command.migration.make']);
        Assert::isInstanceOf(Commands\SeederMakeCommand::class, $app['command.seeder.make']);
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
