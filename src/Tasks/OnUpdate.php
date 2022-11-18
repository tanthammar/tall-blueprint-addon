<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;


class OnUpdate
{
    use MethodsTrait;

    protected const INDENT = '        ';

    //defaults
    protected ?string $session = null; //tall-forms has a notify() method that displays a success message on create/update
    protected ?string $redirect = null; //tall-forms has a save and go back button or save and stay.

    protected function redirect(?string $string): void
    {
        if(config('tall-forms-blueprint.resource-redirect')) {
            $this->redirect = $string;
        }
    }

    protected function session(?string $string): void
    {
        if(config('tall-forms-blueprint.resource-session')) {
            $this->session = $string;
        }
    }

}
