<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;


class OnUpdate
{
    use MethodsTrait;

    const INDENT = '        ';


    protected $session = null;
    protected $redirect = null; //tall-forms has a save and go back button and save and stay.

    protected function redirect($string): void
    {
        if(config('tall-forms-blueprint.resource-redirect')) $this->redirect = $string;
    }

    protected function session($string): void
    {
        if(config('tall-forms-blueprint.resource-session')) $this->session = $string;
    }

}
