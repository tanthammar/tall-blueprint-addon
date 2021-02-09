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


    public function onCreateModel($validated_data)
    {
        // Set the $model property in order to conditionally display fields when the model instance exists
        $this->model = DummyModel::create($validated_data);

        //remove if you do not want to show the delete button or if you are redirecting.
        $this->showDelete = true;

        // create...
    }

    public function onUpdateModel($validated_data)
    {
        $this->model->update($validated_data);
        // update...
    }

    public function onDeleteModel()
    {
        $this->model->delete();
        // delete...
    }


    public function fields()
    {
        return [
            // fields...
        ];
    }
}
