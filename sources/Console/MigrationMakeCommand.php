<?php

namespace Jumilla\Versionia\Laravel\Console;

use Jumilla\Generators\Laravel\OneFileGeneratorCommand as BaseCommand;
use Jumilla\Generators\FileGenerator;

class MigrationMakeCommand extends BaseCommand
{
    /**
     * The console command singature.
     *
     * @var stringphp
     */
    protected $signature = 'make:migration
        {name : The name of the class}
        {--create= : The table to be created}
        {--update= : The table to be updated}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Migration';

    /**
     * The constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setStubDirectory(__DIR__.'/../../stubs');
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        return $this->getRootNamespace().'\\Database\\Migrations';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('create')) {
            return 'migration-create.stub';
        } elseif ($this->option('update')) {
            return 'migration-update.stub';
        } else {
            return 'migration.stub';
        }
    }

    /**
     * Generate file.
     *
     * @param FileGenerator $generator
     * @param string        $path
     * @param string        $fqcn
     *
     * @return bool
     */
    protected function generateFile(FileGenerator $generator, $path, $fqcn)
    {
        list($namespace, $class) = $this->splitFullQualifyClassName($fqcn);

        return $generator->file($path)->template($this->getStub(), [
            'namespace' => $namespace,
            'class' => $class,
            'table' => $this->option('create') ?: $this->option('update'),
        ]);
    }
}
