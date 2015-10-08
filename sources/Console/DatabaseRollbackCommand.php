<?php

namespace Jumilla\Versionia\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Jumilla\Versionia\Laravel\Migrator;

class DatabaseRollbackCommand extends Command
{
    use DatabaseCommandTrait;
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:rollback
        {group : The name of the migration group.}
        {--all : Includes all version.}
        {--force : Force the operation to run when in production.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database migrate to back to one or specifed version';

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

        $group = $this->argument('group');

        // check valid group
        if (!in_array($group, $migrator->migrationGroups())) {
            throw new \InvalidArgumentException("Migation group '$group' is not defined.");
        }

        $version = $this->option('all') ? Migrator::VERSION_NULL : $migrator->migrationLatestVersion($group);

        $migrator->makeLogTable();

        $this->doRollback($migrator, $group, $version);
    }

    /**
     * Execute rollback.
     *
     * @param \Jumilla\Versionia\Laravel\Migrator $migrator
     * @param string                              $target_group
     * @param string                              $target_version
     */
    protected function doRollback(Migrator $migrator, $target_group, $target_version)
    {
        $installed_migrations = $migrator->installedMigrationsByDesc();

        if (!isset($installed_migrations[$target_group])) {
            $this->info("Nothing migrations for group '$target_group'.");

            return;
        }

        foreach ($installed_migrations[$target_group] as $data) {
            // check version
            if ($migrator->compareMigrationVersion($data->version, $target_version) < 0) {
                continue;
            }

            $this->infoDowngrade($target_group, $data->version, $data->class);

            $migrator->doDowngrade($target_group, $data->version);
        }
    }
}
