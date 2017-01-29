<?php

namespace Jumilla\Versionia\Laravel\Commands;

trait DatabaseCommandTrait
{
    /**
     * @param string $group
     * @param string $version
     * @param string $class
     */
    protected function infoUpgrade($group, $version, $class)
    {
        $this->line("<info>Up [{$group}/{$version}]</info> class <comment>{$class}</comment>");
    }

    /**
     * @param string $group
     * @param string $version
     * @param string $class
     */
    protected function infoDowngrade($group, $version, $class)
    {
        $this->line("<info>Down [{$group}/{$version}]</info> class <comment>{$class}</comment>");
    }

    /**
     * @param string $seed
     * @param string $class
     */
    protected function infoSeedRun($seed, $class)
    {
        $this->line("<info>Run [$seed]</info> class <comment>{$class}<comment>");
    }
}
