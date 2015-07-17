<?php

namespace Jumilla\Versionia\Laravel\Support;

use Illuminate\Database\Migrations\Migration as BaseMigration;

abstract class Migration extends BaseMigration
{
    /**
     * Migrate the database to forward.
     *
     * @return void
     */
    abstract public function up();

    /**
     * Migrate the database to backword.
     *
     * @return void
     */
    abstract public function down();
}
