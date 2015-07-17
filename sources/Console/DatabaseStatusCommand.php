<?php

namespace Jumilla\Versionia\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migration;
use Jumilla\Versionia\Laravel\Migrator;

class DatabaseStatusCommand extends Command
{
    use DatabaseCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display migration status';

   /**
    * Execute the console command.
    *
    * @param \Jumilla\Versionia\Laravel\Migrator $migrator
    * @return mixed
    */
   public function handle(Migrator $migrator)
   {
       $migrator->makeLogTable();

       $this->showMigrations($migrator);

       $this->showSeeds($migrator);
   }

    /**
     * Show migration infomation.
     *
     * @param \Jumilla\Versionia\Laravel\Migrator $migrator
     * @return void
     */
    protected function showMigrations(Migrator $migrator)
    {
        $this->line('<comment>Migrations</comment>');

        $groups = $migrator->migrationGroups();

        if (count($groups) > 0) {
            // get [$group => $items]
            $installed_migrations = $migrator->installedMigrationsByDesc();

            foreach ($groups as $group) {
                // [$items] to [$installed_versions]
                $installed_versions = $installed_migrations->get($group, collect())->pluck('version');

                // [$versions] to $latest_version
                $latest_installed_version = $installed_versions->get(0, Migrator::VERSION_NULL);

                // enum definition
                foreach ($migrator->migrationVersionsByDesc($group) as $version => $class) {
                    $installed = $installed_versions->contains($version);

                    if ($version === $latest_installed_version) {
                        $mark = '*';
                    } else {
                        $mark = $installed ? '-' : ' ';
                    }

                    if (!class_exists($class)) {
                        $this->line("{$mark} <info>[{$group}/{$version}]</info> <error>{$class}</error>");
                        $this->line('');
                        $this->error('Error: Class not found.');
                        continue;
                    }

                    $migration = new $class;

                    if (!$migration instanceof Migration) {
                        $this->line("{$mark} <info>[{$group}/{$version}]</info> <error>{$class}</error>");
                        $this->line('');
                        $this->error('Error: Must inherit from class "Illuminate\Database\Migrations\Migration".');
                        continue;
                    }

                    $this->line("{$mark} <info>[{$group}/{$version}]</info> {$class}");
                }

                $this->line('');
            }
        } else {
            $this->info('Nothing.');
            $this->line('');
        }
    }

    /**
     * Show seed infomation.
     *
     * @param \Jumilla\Versionia\Laravel\Migrator $migrator
     * @return void
     */
    protected function showSeeds(Migrator $migrator)
    {
        $this->line('<comment>Seeds</comment>');

        $seeds = $migrator->seedNames();

        if (count($seeds) > 0) {
            $default_seed = $migrator->defaultSeed();

            foreach ($seeds as $seed) {
                $class = $migrator->seedClass($seed);

                $status_mark = ' ';
                $default_mark = $seed == $default_seed ? '(default)' : '';

                if (!class_exists($class)) {
                    $this->line("{$status_mark} <info>[{$seed}]</info> <error>{$class}</error>");
                    $this->line('');
                    $this->error('Error: Class not found.');
                    continue;
                }

                $this->line("{$status_mark} <comment>{$default_mark}</comment><info>[{$seed}]</info> {$class}");
            }

            if ($default_seed && !in_array($default_seed, $seeds)) {
                $this->line('');
                $this->error("Error: default seed '{$default_seed}' is not defined.");
            }
        } else {
            $this->info('Nothing.');
        }

        $this->line('');
    }
}
