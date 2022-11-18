<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;


class OnDelete
{
    use MethodsTrait;

    protected const INDENT = '        ';

    protected string $session = self::INDENT . 'session()->flash("success", "The "' . ' . class_basename($this->model). ' . '" was deleted");' . PHP_EOL;

    protected string $redirect = self::INDENT . 'return redirect(urldecode($this->previous));' . PHP_EOL;

    protected function redirect(string $string): void
    {
        //return redirect as set in draft.yaml delete requires a redirect
        //controllers resource shorthand auto generates a redirect
        $this->redirect = $string;
    }

    protected function session(string $string): void
    {
        //best to flash to session as deletes generates a redirect
        $this->session = $string;
    }

}
