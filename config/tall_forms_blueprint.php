<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resource Timestamps
    |--------------------------------------------------------------------------
    |
    | The default is to add the timestamp fields 'created_at',
    | 'updated_at' and 'deleted_at' (if model uses SoftDeletes Trait) to
    | the generated files. If you want to prevent the generator from
    | adding these fields set this option to `false`.
    |
    */

    'timestamps' => true,

    //no trailing back-slash, in relation to the "app" directory
    'forms-output-path' => '\\Http\\Livewire\\Forms',

    //where applicable, add ->includeExternalScripts()
    'include-external-scripts' => false,

    'generators' => [
        'controller' => \Blueprint\Generators\ControllerGenerator::class,
        'view' => \Blueprint\Generators\Statements\ViewGenerator::class,
    ],
];
