<?php

use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Console\SeederMakeCommand as Command;

class SeederMakeCommandTests extends TestCase
{
    use ConsoleCommandTrait;

    public function test_withNoParameter()
    {
        // 1. setup
        $app = $this->createApplication();
        $migrator = $this->createMigrator();
        $command = new Command();

        // 2. condition

        // 3. test
        try {
            $this->runCommand($app, $command, []);
            Assert::failure();
        } catch (RuntimeException $ex) {
            Assert::success();
        }
    }
}
