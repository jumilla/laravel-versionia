<?php

namespace Jumilla\Versionia\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Jumilla\Versionia\Laravel\Migrator;

class DatabaseUpgradeCommand extends Command
{
    use DatabaseCommandTrait;
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:upgrade
        {--seed= : Indicates if the seed task should be re-run.}
        {--force : Force the operation to run when in production.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database migration to latest version';

    /**
     * Execute the console command.
     *
     * @param \Jumilla\Versionia\Laravel\Migrator $migrator
     *
     * @return mixed
     */
    public function handle(Migrator $migrator)
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $migrator->makeLogTable();

        $this->migrateToLatest($migrator);

        $seed = $this->option('seed');

        if ($seed) {
            $this->call('database:seed', ['name' => $seed, '--force' => true]);
        }
    }

    /**
     * Migrate dataase to latest version.
     *
     * @param \Jumilla\Versionia\Laravel\Migrator $migrator
     */
    protected function migrateToLatest(Migrator $migrator)
    {
        $installed_migrations = $migrator->installedLatestMigrations();

        $migration_count = 0;

        foreach ($migrator->migrationGroups() as $group) {
            // [$group => ['version'=>$version, 'class'=>$class]] to $version
            $latest_installed_version = data_get($installed_migrations, $group.'.version', Migrator::VERSION_NULL);

            foreach ($migrator->migrationVersions($group) as $version => $class) {
                if ($migrator->compareMigrationVersion($version, $latest_installed_version) > 0) {
                    $this->line("<info>Up [$group/$version]</info> Run class <comment>$class</comment>");

                    $migrator->doUpgrade($group, $version, $class);

                    ++$migration_count;
                }
            }
        }

        if ($migration_count == 0) {
            $this->line('<info>Nothing to migrate.</info>');
        }
    }
}
