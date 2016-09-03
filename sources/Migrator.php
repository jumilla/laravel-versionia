<?php

namespace Jumilla\Versionia\Laravel;

use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Migrator
{
    const VERSION_NULL = null;

    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

    /**
     * @var string
     */
    protected $table = 'migrations';

    /**
     * @var array
     */
    protected $migrations = [];

    /**
     * @var array
     */
    protected $seeds = [];

    /**
     * @var string
     */
    protected $default_seed;

    /**
     * @param \Illuminate\Contracts\Database\DatabaseManager $db
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(DatabaseManager $db, Config $config = null)
    {
        $this->db = $db;
        if ($config) {
            $this->table = $config->get('database.migrations', $this->table);
        }
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $a
     * @param string $b
     *
     * @return int
     */
    public function compareMigrationVersion($a, $b)
    {
        return version_compare($a, $b);
    }

    /**
     * @param string $group
     * @param string $version
     * @param string $class
     */
    public function registerMigration($group, $version, $class)
    {
        $this->registerMigrations($group, [
            $version => $class,
        ]);
    }

    /**
     * @param string $group
     * @param array  $versions
     */
    public function registerMigrations($group, array $versions)
    {
        foreach ($versions as $version => $class) {
            $this->migrations[$group][$version] = $class;
        }
    }

    /**
     * @return array
     */
    public function migrationGroups()
    {
        $groups = array_keys($this->migrations);

        sort($groups);

        return $groups;
    }

    /**
     * @param string $group
     * @param bool   $descending
     *
     * @return array
     */
    public function migrationVersions($group, $descending = false)
    {
        $versions = array_get($this->migrations, $group, []);

        uksort($versions, [$this, 'compareMigrationVersion']);

        return $descending ? array_reverse($versions, true) : $versions;
    }

    /**
     * @param string $group
     *
     * @return array
     */
    public function migrationVersionsByDesc($group)
    {
        return $this->migrationVersions($group, true);
    }

    /**
     * @param string $group
     *
     * @return string
     */
    public function migrationLatestVersion($group)
    {
        foreach ($this->migrationVersionsByDesc($group) as $version => $class) {
            return $version;
        }

        return self::VERSION_NULL;
    }

    /**
     * @param string $group
     * @param string $version
     *
     * @return string
     */
    public function migrationClass($group, $version)
    {
        $versions = array_get($this->migrations, $group, []);

        return array_get($versions, $version, null);
    }

    /**
     * @param string $name
     * @param string $class
     */
    public function registerSeed($name, $class)
    {
        $this->registerSeeds([
            $name => $class,
        ]);
    }

    /**
     * @param array $seeds
     */
    public function registerSeeds(array $seeds)
    {
        $this->seeds = array_merge($this->seeds, $seeds);
    }

    /**
     * @return string
     */
    public function defaultSeed()
    {
        return $this->default_seed;
    }

    /**
     * @param string $seed
     */
    public function setDefaultSeed($seed)
    {
        $this->default_seed = $seed;
    }

    /**
     * @return array
     */
    public function seedNames()
    {
        return array_keys($this->seeds);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function seedClass($name)
    {
        return array_get($this->seeds, $name);
    }

    /**
     */
    public function makeLogTable()
    {
        if (!Schema::hasTable($this->table)) {
            Schema::create($this->table, function (Blueprint $table) {
                $table->string('group');
                $table->string('version');
                $table->string('class');

                $table->unique(['group', 'version']);
            });
        }
    }

    /**
     * @param bool $descending
     *
     * @return \Illuminate\Support\Collection
     */
    public function installedMigrations($descending = false)
    {
        return collect($this->db->table($this->table)->get())->sort(function ($a, $b) use ($descending) {
            return $this->compareMigrationVersion($a->version, $b->version) * ($descending ? -1 : 1);
        })->groupBy('group');
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function installedMigrationsByDesc()
    {
        return $this->installedMigrations(true);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function installedLatestMigrations()
    {
        return collect($this->db->table($this->table)->get())->reduce(function ($result, $item) {
            if (isset($result[$item->group])) {
                if ($this->compareMigrationVersion($item->version, $result[$item->group]->version) > 0) {
                    $result[$item->group] = $item;
                }
            } else {
                $result[$item->group] = $item;
            }

            return $result;
        }, collect());
    }

    /**
     * @param string $group
     * @param string $version
     * @param string $class
     */
    public function addMigrationLog($group, $version, $class)
    {
        $this->db->table($this->table)->insert([
            'group' => $group,
            'version' => $version,
            'class' => $class,
        ]);
    }

    /**
     * @param string $group
     * @param string $version
     */
    public function removeMigrationLog($group, $version)
    {
        $this->db->table($this->table)->where('group', $group)->where('version', $version)->delete();
    }

    /**
     * @param string $group
     * @param string $version
     * @param string $class
     */
    public function doUpgrade($group, $version, $class)
    {
        $migration = new $class();

        $migration->up();

        $this->addMigrationLog($group, $version, $class);
    }

    /**
     * @param string $group
     * @param string $version
     * @param string $class
     */
    public function doDowngrade($group, $version, $class = null)
    {
        if ($class === null) {
            $class = $this->migrationClass($group, $version);
        }

        $migration = new $class();

        $migration->down();

        $this->removeMigrationLog($group, $version);
    }
}
