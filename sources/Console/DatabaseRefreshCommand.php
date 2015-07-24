<?php

namespace Jumilla\Versionia\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Jumilla\Versionia\Laravel\Migrator;

class DatabaseRefreshCommand extends Command
{
    use DatabaseCommandTrait;
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:refresh
        {--seed= : Indicates if the seed task should be re-run.}
        {--force : Force the operation to run when in production.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database migrate to clean, and migrate to latest version';

    /**
     * Execute the console command.
     *
     * @param \Jumilla\Versionia\Laravel\Migrator $migrator
     * @return mixed
     */
    public function handle(Migrator $migrator)
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $migrator->makeLogTable();

        $this->doRefresh($migrator);

        $seed = $this->option('seed');

        if ($seed) {
            $this->call('database:seed', ['name' => $seed, '--force' => true]);
        }
    }

    /**
     * Execute clean and upgrade.
     *
     * @param \Jumilla\Versionia\Laravel\Migrator $migrator
     * @return void
     */
    protected function doRefresh(Migrator $migrator)
    {
        // retreive installed versions
        $installed_migrations = $migrator->installedMigrationsByDesc();

        // downgrade
        foreach ($installed_migrations as $group => $migrations) {
            foreach ($migrations as $data) {
                $this->infoDowngrade($group, $data->version, $data->class);

                $migrator->doDowngrade($group, $data->version);
            }
        }

        // upgrade
        foreach ($migrator->migrationGroups() as $group) {
            foreach ($migrator->migrationVersions($group) as $version => $class) {
                $this->infoUpgrade($group, $version, $class);

                $migrator->doUpgrade($group, $version, $class);
            }
        }
    }
}
