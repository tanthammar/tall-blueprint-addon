<?php

namespace DummyNamespace;

use ModelsPath;
use Tanthammar\TallForms\TallFormComponent;
use Controllers;

class DummyModelForm extends TallFormComponent
{
    public function mount(?DummyModel $dummymodel)
    {
        //Gate::authorize()
        $this->fill([
            'formTitle' => 'Create & Edit DummyModel',
            'wrapWithView' => true, //see https://github.com/tanthammar/tall-forms/wiki/installation/Wrapper-Layout
            'showSave' => true,
            'showReset' => true,
            'showDelete' => $dummymodel->exists ? true : false, //see https://github.com/tanthammar/tall-forms/wiki/Form-Methods#delete
            'showGoBack' => true,
        ]);
        $this->mount_form($dummymodel); // $dummymodel from hereon, called $this->model
    }


    // REQUIRED, if you are creating a model with this form
    public function onCreateModel($validated_data)
    {
        // create...
    }

    // OPTIONAL, method exists in tall-form component
    public function onUpdateModel($validated_data)
    {
        // update...
    }

    // OPTIONAL, method exists in tall-form component
    public function onDeleteModel()
    {
        $dummymodel = $this->model;
        // delete...
    }


    public function fields()
    {
        return [
            // fields...
        ];
    }
}
