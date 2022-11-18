<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;


use Illuminate\Support\Str;

trait HasSharedGeneratorFunctions
{
    protected function sharedStrReplace(string $stub, $name, $className): array|string
    {
        return str_replace(
                ['DummyNamespace', 'ModelsPath', 'DummyModel', 'dummymodel'],
                ["App" . $this->getFormNamespace(), $className, $name, Str::snake($name)],
            $stub);
    }

    protected function getFormNamespace(): string
    {
        return
            str_replace('App\\', '\\', config('livewire.class_namespace'))
            . '\\' .
            config('tall-forms-blueprint.forms-output-path', 'Forms');
    }
}
