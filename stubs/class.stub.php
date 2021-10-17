<?php

namespace DummyNamespace;

use ModelsPath;
use Tanthammar\TallForms\TallFormComponent;
use Controllers;

class DummyModelForm extends TallFormComponent
{
    public function mount(?DummyModel $dummymodel)
    {
        //Gate::authorize();
        $this->mount_form($dummymodel); // $dummymodel from hereon, called $this->model
    }

    protected function formAttr(): array
    {
        $dummymodel = $this->model;
        
        return [
            'formTitle' => 'Create & Edit DummyModel',
            'wrapWithView' => true, //see https://github.com/tanthammar/tall-forms/wiki/installation/Wrapper-Layout
            'showSave' => true,
            'showReset' => true,
            'showDelete' => $dummymodel->exists ? true : false, //see https://github.com/tanthammar/tall-forms/wiki/Form-Methods#delete
            'showGoBack' => true,
        ];
    }


    // REQUIRED, if you are creating a model with this form
    protected function onCreateModel($validated_data)
    {
        // create...
    }

    // OPTIONAL, method exists in tall-form component
    protected function onUpdateModel($validated_data)
    {
        // update...
    }

    // OPTIONAL, method exists in tall-form component
    protected function onDeleteModel()
    {
        $dummymodel = $this->model;
        // delete...
    }


    protected function fields()
    {
        return [
            // fields...
        ];
    }
}
