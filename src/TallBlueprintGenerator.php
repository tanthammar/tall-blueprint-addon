<?php

namespace Tanthammar\TallBlueprintAddon;

use Blueprint\Contracts\Generator;
use Blueprint\Models\Model;
use Blueprint\Tree;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use Tanthammar\TallBlueprintAddon\Contracts\Task;
use Tanthammar\TallBlueprintAddon\Tasks\AddTimestampFields;
use Tanthammar\TallBlueprintAddon\Tasks\HasSharedGeneratorFunctions;
use Tanthammar\TallBlueprintAddon\Tasks\RemapImports;

class TallBlueprintGenerator implements Generator
{
    use HasStubPath, HasSharedGeneratorFunctions;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $files;

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

        $stub = $this->getStub();

        /** @var \Blueprint\Models\Model $model */
        foreach ($tree->models() as $model) {

            $path = $this->outputPath($model->name());
            $this->files->put($path, $this->populateStub($stub, $model));

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
        $this->imports = array_unique(array_merge($this->imports, $data['imports']));
        $stub = str_replace('use Tanthammar\TallForms\TallFormComponent;', implode(PHP_EOL, $data['imports']), $stub);

        return $stub;
    }


    public function registerTask(Task $task): void
    {
        $this->tasks[get_class($task)] = $task;
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
