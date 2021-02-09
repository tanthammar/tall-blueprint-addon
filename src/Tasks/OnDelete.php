<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;


class OnDelete
{
    use MethodsTrait;

    const INDENT = '        ';

    protected $session = self::INDENT."session()->flash('success', __('The object was deleted'));". PHP_EOL;
    protected $redirect = self::INDENT.'return redirect(urldecode($this->previous));'. PHP_EOL;

    protected function crudAction() {
        data_set($this->data, 'delete', $this->buildMethods($this->statements));
    }

    protected function redirect($string): void
    {
        //return redirect as set in draft.yaml delete requires a redirect
        //controllers resource shorthand auto generates a redirect
        $this->redirect = $string;
    }

    protected function session($string): void
    {
        //best to flash to session as deletes generates a redirect
        $this->session = $string;
    }

}
