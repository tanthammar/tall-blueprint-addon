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

    //no trailing back-slash, in relation to config('livewire.class_namespace') directory
    'forms-output-path' => 'Forms',

    //where applicable, add ->includeExternalScripts()
    'include-external-scripts' => false,

    //set to true if you want redirects on CREATE and UPDATE form methods.
    //only applicable if you use the blueprint controllers RESOURCE SHORTHAND
    //tall-forms has a save-and-stay, and a save-and-go-back button, setting this option to true REPLACES that behaviour
    'resource-redirect' => false,

    //tall-forms has a notify() method that displays a success message on create/update, maybe you don't need to flash to session?
    //only applicable if you use the blueprint controllers RESOURCE SHORTHAND
    'resource-session' => false,

    //Do you have access to the tall-forms-sponsor repository?
    //If not, please sponsor me: https://github.com/sponsors/tanthammar
    //Setting this to true, will output sponsor fields instead of open source versions. Like DatePicker instead of Input->type('datetime-local').
    'sponsor' => false,
];
