<?php

namespace Jumilla\Versionia\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Jumilla\Versionia\Laravel\Migrator;

class DatabaseSeedCommand extends Command
{
    use DatabaseCommandTrait;
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:seed
        {name? : the name of seed.}
        {--force : Force the operation to run when in production.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert seed to database';

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

        $seed = $this->argument('name') ?: $migrator->defaultSeed();

        if (!$seed) {
            $this->error('Default seed is not defined.');

            return;
        }

        $class = $migrator->seedClass($seed);

        if (!$class) {
            $this->error("Seed '$seed' is not defined.");

            return;
        }

        $this->infoSeedRun($seed, $class);

        $seeder = new $class;

        $seeder->setCommand($this)->run();
    }
}
