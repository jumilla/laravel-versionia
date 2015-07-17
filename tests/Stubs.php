<?php

use Jumilla\Versionia\Laravel\Support\Migration;
use Jumilla\Versionia\Laravel\Support\Seeder;
use Jumilla\Versionia\Laravel\Support\DatabaseServiceProvider;

class FooMigration extends Migration
{
    public function up()
    {
    }

    public function down()
    {
    }
}

class FooMigration_NoInheritMigrationClass
{
}

class FooSeeder extends Seeder
{
    public function run()
    {
    }
}

class DatabaseServiceProviderStub extends DatabaseServiceProvider
{
    public function boot()
    {
    }

    public function __call($method, $arguments)
    {
        call_user_func_array([$this, $method], $arguments);
    }
}
