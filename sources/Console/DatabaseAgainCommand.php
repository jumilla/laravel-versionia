<?php

namespace Jumilla\Versionia\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Jumilla\Versionia\Laravel\Migrator;

class DatabaseAgainCommand extends Command
{
    use DatabaseCommandTrait;
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:again
        {group : Group name}
        {--seed= : Indicates if the seed task should be re-run.}
        {--force : Force the operation to run when in production.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrations rollback latest version and upgrade';

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

        $migrator->makeLogTable();

        $this->doAgain($migrator, $group);

        $seed = $this->option('seed');

        if ($seed) {
            $this->call('database:seed', ['name' => $seed, '--force' => true]);
        }
    }

    /**
     * Execute rollback and upgrade one version.
     *
     * @param \Jumilla\Versionia\Laravel\Migrator $migrator
     * @param string                              $group
     */
    protected function doAgain(Migrator $migrator, $group)
    {
        // retreive installed versions
        $installed_migrations = $migrator->installedLatestMigrations();

        $installed_version = data_get($installed_migrations, $group.'.version', Migrator::VERSION_NULL);

        $definition_versions = $migrator->migrationVersionsByDesc($group);

        if (!$this->checkInstalledVersion($installed_version, $definition_versions)) {
            return;
        }

        // remove migration log
        $definition_latest_version = array_get(array_keys($definition_versions), 0, Migrator::VERSION_NULL);
        if ($migrator->compareMigrationVersion($installed_version, $definition_latest_version) >= 0) {
            $this->line("<info>Remove log [$group/$installed_version]</info>");
            $migrator->removeMigrationLog($group, $installed_version);
        }

        // downgrade & upgrade
        foreach ($definition_versions as $version => $class) {
            $this->infoDowngrade($group, $version, $class);

            $migrator->doDowngrade($group, $version, $class);

            $this->infoUpgrade($group, $version, $class);

            $migrator->doUpgrade($group, $version, $class);

            break;
        }
    }

    /**
     * Check installed version.
     *
     * @param string $installed_version
     * @param array  $definition_versions
     *
     * @return bool
     */
    protected function checkInstalledVersion($installed_version, array $definition_versions)
    {
        if ($installed_version === null) {
            $this->error('Nothing installed version.');
            $this->line('Please run <comment>database:upgrade<comment>.');

            return false;
        }

        $index = array_search($installed_version, array_keys($definition_versions));

        if ($index === false) {
            $versions = json_encode(array_keys($definition_versions));
            $this->error("Installed version '{$installed_version}' was not found in definition {$versions}.");

            return false;
        }

        if ($index >= 2) {
            $this->error("Installed version '$installed_version' was older.");
            $this->line('Please run <comment>database:upgrade<comment>.');

            return false;
        }

        return true;
    }
}
