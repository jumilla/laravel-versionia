<?php

namespace Jumilla\Versionia\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Jumilla\Versionia\Laravel\Migrator;

class DatabaseCleanCommand extends Command
{
    use DatabaseCommandTrait;
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:clean
        {--force : Force the operation to run when in production.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Database migrate to clean';

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

        $installed_migrations = $migrator->installedMigrationsByDesc();

        foreach ($installed_migrations as $group => $migrations) {
            foreach ($migrations as $data) {
                $this->infoDowngrade($group, $data->version, $data->class);

                $migrator->doDowngrade($group, $data->version);
            }
        }
    }
}
