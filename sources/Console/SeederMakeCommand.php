<?php

namespace Jumilla\Versionia\Laravel\Console;

use Jumilla\Generators\Laravel\OneFileGeneratorCommand as BaseCommand;
use Jumilla\Generators\FileGenerator;

class SeederMakeCommand extends BaseCommand
{
    /**
     * The console command singature.
     *
     * @var stringphp
     */
    protected $signature = 'make:seeder
        {name : The name of the class}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new seeder class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Seeder';

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
        return $this->getRootNamespace().'\\Database\\Seeds';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return 'seeder.stub';
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
        ]);
    }
}
