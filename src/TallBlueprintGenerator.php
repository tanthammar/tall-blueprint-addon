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

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function output(Tree $tree): array
    {
        $output = [];

        $stub = $this->files->get($this->stubPath().DIRECTORY_SEPARATOR.'class.stub.php');

        /** @var \Blueprint\Models\Model $model */
        foreach ($tree->models() as $model) {

            $path = "app".config('tall_forms_blueprint.forms-output-path');

            if (! $this->files->exists(dirname($path))) {
                $this->files->makeDirectory(dirname($path), 0755, true);
            }

            $this->files->put($path, $this->populateStub($stub, $model));

            $output['created'][] = $path;
        }

        return $output;
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

        $stub = str_replace('DummyNamespace', "App".config('tall_forms_blueprint.forms-output-path'), $stub);
        $stub = str_replace('DummyClass', $model->name(), $stub);
        $stub = str_replace('DummyModel', $model->name(), $stub);
        $stub = str_replace('// fields...', $data['fields'], $stub);
        $stub = str_replace('use Tanthammar\TallForms\TallFormComponent;', implode(PHP_EOL, $data['imports']), $stub);

        return $stub;
    }

    protected function getFormNamespace(Model $model): string
    {
        $namespace = Str::after($model->fullyQualifiedNamespace(), config('blueprint.namespace'));
        $namespace = config('blueprint.namespace').config('tall_forms_blueprint.forms-output-path').$namespace;

        if (config('blueprint.models_namespace')) {
            $namespace = str_replace('\\'.config('blueprint.models_namespace'), '', $namespace);
        }

        return $namespace;
    }

    public function registerTask(Task $task): void
    {
        $this->tasks[get_class($task)] = $task;
    }

    public function removeTask(string $taskName)
    {
        $taskClassNames = array_map(function ($taskObj) {
            return get_class($taskObj);
        }, $this->tasks);

        $targetIndex = array_search($taskName, $taskClassNames);
        array_splice($this->tasks, $targetIndex, 1);
    }

    protected function filteredTasks(): array
    {
        $tasks = $this->tasks;

        if (! config('tall_forms_blueprint.timestamps')) {
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
