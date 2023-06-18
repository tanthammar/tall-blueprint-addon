<?php

namespace Tanthammar\TallBlueprintAddon;

use Blueprint\Contracts\Generator;
use Blueprint\Models\Controller;
use Blueprint\Tree;
use Illuminate\Filesystem\Filesystem;
use Tanthammar\TallBlueprintAddon\Tasks\HasSharedGeneratorFunctions;
use Tanthammar\TallBlueprintAddon\Tasks\OnCreate;
use Tanthammar\TallBlueprintAddon\Tasks\OnDelete;
use Tanthammar\TallBlueprintAddon\Tasks\OnUpdate;

class TallMethodsBlueprintGenerator implements Generator
{
    use HasStubPath, HasSharedGeneratorFunctions;

    protected ?Filesystem $files;

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function output(Tree $tree): array
    {
        $output = [];

        $stub = $this->getStub();

        /** @var Controller $controller */
        foreach ($tree->controllers() as $controller) {
            if (!$controller->isApiResource()) {
                $path = $this->outputPath($controller->name());
                $stub = $this->files->exists($path) ? $this->files->get($path) : $stub;
                $this->files->put($path, $this->populateStub($stub, $controller));
            }
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


    protected function populateStub(string $stub, Controller $controller): array|string
    {
        $data = [];

        foreach ($controller->methods() as $name => $statements) {
            data_set($data, 'name', $controller->name());
            //switch action name
            if ($name === 'store') {
                data_set($data, 'action', 'create');
                $data = (new onCreate($statements, $data))->handle();
            }
            if ($name === 'update') {
                data_set($data, 'action', 'update');
                $data = (new onUpdate($statements, $data))->handle();
            }
            if ($name === 'destroy') {
                data_set($data, 'action', 'delete');
                $data = (new onDelete($statements, $data))->handle();
            }
        }

        $stub = str_replace(
            ['// create...', '// update...', '// delete...'],
            [data_get($data, 'create'), data_get($data, 'update'), data_get($data, 'delete')],
            $stub);

        $stub = (string)$this->sharedStrReplace($stub, $controller->name(), $controller->fullyQualifiedClassName());

        $imports = array_unique(data_get($data, 'imports', []));

        return str_replace('use Controllers;', implode(PHP_EOL, $imports), $stub);
    }


    public function types(): array
    {
        return ['tall-forms-methods'];
    }
}
