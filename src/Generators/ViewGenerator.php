<?php


namespace Tanthammar\TallBlueprintAddon\Generators;


use Blueprint\Contracts\Generator;
use Blueprint\Models\Statements\RenderStatement;
use Blueprint\Tree;

class ViewGenerator implements Generator
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    private $files;

    const tall_forms_actions = [
        'create', 'store', 'edit', 'update', 'destroy'
    ];

    /**
     * @var bool
     */
    private $is_tall_form = false;

    private $model = '';

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function output(Tree $tree): array
    {
        $output = [];

        $stub = $this->files->stub('view.stub');

        /** @var \Blueprint\Models\Controller $controller */
        foreach ($tree->controllers() as $controller) {
            foreach ($controller->methods() as $method => $statements) {
                //start tall-forms
                if (in_array($method, self::tall_forms_actions)) {
                    $this->is_tall_form = true;
                    $this->model = \Str::lower($controller->name());
                } else {
                    $this->is_tall_form = false;
                    $this->model = '';
                }
                //end tall-forms

                foreach ($statements as $statement) {
                    if (!$statement instanceof RenderStatement) {
                        continue;
                    }

                    $path = $this->getPath($statement->view());

                    if ($this->files->exists($path)) {
                        continue;
                    }

                    if (!$this->files->exists(dirname($path))) {
                        $this->files->makeDirectory(dirname($path), 0755, true);
                    }

                    $this->files->put($path, $this->populateStub($stub, $statement));

                    $output['created'][] = $path;
                }
            }
        }

        return $output;
    }

    public function types(): array
    {
        return ['controllers', 'views'];
    }

    protected function getPath(string $view)
    {
        return 'resources/views/' . str_replace('.', '/', $view) . '.blade.php';
    }

    protected function populateStub(string $stub, RenderStatement $renderStatement): string
    {
        if ($this->is_tall_form) {
            $stub = str_replace('{{--', null, $stub);
            $stub = str_replace('--}}', null, $stub);
            $template = '<livewire:forms.' . $this->model . '-form :' . $this->model . '="$' . $this->model . '"/>';
            $stub = str_replace('{{ view }} template', $template, $stub);
        } else {
            $stub = str_replace('{{ view }}', $renderStatement->view(), $stub);
        }
        return $stub;
    }
}
