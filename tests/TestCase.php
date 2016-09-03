<?php

use Illuminate\Database\DatabaseManager;
use Jumilla\Versionia\Laravel\Migrator;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @after
     */
    public function teardownSandbox()
    {
        $this->removeDirectory(__DIR__.'/sandbox');
    }

    protected function createApplication(array $mocks = [])
    {
        $app = $this->app = new ApplicationStub(array_merge([
            'db' => DatabaseManager::class,
        ], $mocks));

        return $app;
    }

    protected function createMigrator(array $overrides = null)
    {
        $migrator = $this->createMock(Migrator::class, $overrides, function () {
            return [$this->app['db']];
        });

        $this->app->instance('database.migrator', $migrator);
        $this->app->alias('database.migrator', Migrator::class);

        return $migrator;
    }

    /**
     * Recursively delete a directory
     *
     * @param string $dir Directory name
     */
    function removeDirectory($dir)
    {
        if (!$dh = @opendir($dir)) {
            return;
        }

        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }

            $path = $dir.'/'.$obj;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            }
            else {
                unlink($path);
            }
        }

        closedir($dh);
        @rmdir($dir);
    }
}
