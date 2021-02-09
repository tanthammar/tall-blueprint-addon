<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;


use Illuminate\Support\Str;

trait HasSharedGeneratorFunctions
{
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
}
