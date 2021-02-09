<?php

namespace Tanthammar\TallBlueprintAddon;

use Blueprint\Blueprint;
use Blueprint\Contracts\Generator;
use Blueprint\Models\Model;
use Blueprint\Tree;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use Tanthammar\TallBlueprintAddon\Contracts\Task;
use Tanthammar\TallBlueprintAddon\Tasks\AddTimestampFields;
use Tanthammar\TallBlueprintAddon\Tasks\RemapImports;

class TallBlueprintGenerator implements Generator
{
    use HasStubPath;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    private $files;

    /** @var array */
    private $imports = [];

    /** @var array */
    private $tasks = [];

    /** @var array */
    private $controllerTasks = [];

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function output(Tree $tree): array
    {
        $output = [];

        $stub = $this->files->get($this->stubPath() . DIRECTORY_SEPARATOR . 'class.stub.php');

        /** @var \Blueprint\Models\Model $model */
        foreach ($tree->models() as $model) {

            $path = $this->outputPath($model->name());

            $this->files->put($path, $this->populateStub($stub, $model));

            $output['created'][] = $path;
        }

        /** @var \Blueprint\Models\Controller $controller */
        foreach ($tree->controllers() as $controller) {
            $path = $this->outputPath($controller->name());
            $this->files->put($path, $this->populateControllerStub($stub, $controller));

            $output['created'][] = $path;
        }

        return $output;
    }

    protected function outputPath($name): string
    {
        $path = "app" . str_replace('\\', '/', $this->getFormNamespace()) . '/' . $name . 'Form.php';

        if (!$this->files->exists(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }
        return $path;
    }


    protected function populateStub(string $stub, Model $model): string
    {
        $data = resolve(Pipeline::class)
            ->send([
                'fields' => '',
                'imports' => [],
                'model' => $model,
            ])
            ->through($this->filteredTasks())
            ->thenReturn();


        $stub = $this->sharedStrReplace($stub, $model->name(), $model->fullyQualifiedClassName());
        $stub = str_replace('// fields...', $data['fields'], $stub);
        $stub = str_replace('use Tanthammar\TallForms\TallFormComponent;', implode(PHP_EOL, $data['imports']), $stub);

        return $stub;
    }

    protected function populateControllerStub(string $stub, \Blueprint\Models\Controller $controller)
    {
        $data = resolve(Pipeline::class)
            ->send([
                'create' => '',
                'update' => '',
                'delete' => '',
                'imports' => [],
                'controller' => $controller,
            ])
            ->through($this->controllerTasks)
            ->thenReturn();

        $stub = $this->sharedStrReplace($stub, $controller->name(), $controller->fullyQualifiedClassName());
        $stub = str_replace('// create...', $data['create'], $stub);
        $stub = str_replace('// update...', $data['update'], $stub);
        $stub = str_replace('// delete...', $data['delete'], $stub);
        $stub = str_replace('use Controllers;', implode(PHP_EOL, $data['imports']), $stub);

        return $stub;
    }

    protected function sharedStrReplace(string $stub, $name, $className)
    {
        $stub = str_replace('DummyNamespace', "App" . $this->getFormNamespace(), $stub);
        $stub = str_replace('ModelsPath', $className, $stub);
        $stub = str_replace('DummyModel', $name, $stub);
        $stub = str_replace('dummymodel', Str::snake($name), $stub);
        return $stub;
    }



    protected function getFormNamespace(): string
    {
        return
            str_replace('App\\', '\\', config('livewire.class_namespace'))
            . '\\' .
            config('tall-forms-blueprint.forms-output-path', 'Forms');
    }

    public function registerTask(Task $task): void
    {
        $this->tasks[get_class($task)] = $task;
    }

    public function registerControllerTask(Task $task): void
    {
        $this->controllerTasks[get_class($task)] = $task;
    }


    protected function filteredTasks(): array
    {
        $tasks = $this->tasks;

        if (!config('tall-forms-blueprint.timestamps')) {
            $tasks = array_filter($tasks, function ($key) {
                return $key !== AddTimestampFields::class;
            }, ARRAY_FILTER_USE_KEY);
        }

        return array_merge($tasks, [new RemapImports]);
    }

    public function types(): array
    {
        return ['tall-forms'];
    }
}
