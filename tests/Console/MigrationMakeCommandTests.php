<?php

use Jumilla\Versionia\Laravel\Migrator;
use Jumilla\Versionia\Laravel\Console\MigrationMakeCommand as Command;

class MigrationMakeCommandTests extends TestCase
{
    use ConsoleCommandTrait;

    public function test_withNoParameter()
    {
        // 1. setup
        $app = $this->createApplication();
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

    /**
     * @test
     */
    public function test_withName_typeBlank()
    {
        // 1. setup
        $app = $this->createApplication();
        $command = new Command();

        // 2. condition

        // 3. test
        $this->runCommand($app, $command, ['name' => 'foo']);
    }

    /**
     * @test
     */
    public function test_withName_typeCreate()
    {
        // 1. setup
        $app = $this->createApplication();
        $command = new Command();

        // 2. condition

        // 3. test
        $this->runCommand($app, $command, ['name' => 'foo', '--create' => true]);
    }

    /**
     * @test
     */
    public function test_withName_typeUpdate()
    {
        // 1. setup
        $app = $this->createApplication();
        $command = new Command();

        // 2. condition

        // 3. test
        $this->runCommand($app, $command, ['name' => 'foo', '--update' => true]);
    }
}
