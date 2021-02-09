<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;

class OnCreate
{
    use MethodsTrait;

    const INDENT = '        ';

    protected $session = null;
    protected $redirect = null; //tall-forms has a save and go back button or save and stay.

    protected function crudAction() {
        data_set($this->data, 'create', $this->buildMethods($this->statements));
    }

    protected function redirect($string): void
    {
        if(config('tall-forms-blueprint.resource-redirect')) $this->redirect = $string;
    }

    protected function session($string): void
    {
        if(config('tall-forms-blueprint.resource-session')) $this->session = $string;
    }

}
