<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class RepositoryGenerator extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new repository class.';


    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/repository.stub';
    }


    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $stub = $this->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
            
        $stub = $this->replaceModel($stub, $name);

        return $stub;
    }

    /**
     * Replace the class model for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceModel($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        $class = str_replace('Repository', '', $class);

        $stub = str_replace('DummyModel', $class, $stub);
        $stub = str_replace('DummyInstance', strtolower($class), $stub);

        return $stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }
}
