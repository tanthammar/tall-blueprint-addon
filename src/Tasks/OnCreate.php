<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;

class OnCreate
{
    use MethodsTrait;

    const INDENT = '        ';

    //defaults
    protected $session = null; //tall-forms has a notify() method that displays a success message on create/update
    protected $redirect = null; //tall-forms has a save and go back button or save and stay.

    protected function redirect($string): void
    {
        if(config('tall-forms-blueprint.resource-redirect')) $this->redirect = $string;
    }

    protected function session($string): void
    {
        if(config('tall-forms-blueprint.resource-session')) $this->session = $string;
    }

}
