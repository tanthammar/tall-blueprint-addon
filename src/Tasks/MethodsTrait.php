<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;


use Blueprint\Models\Statements\DispatchStatement;
use Blueprint\Models\Statements\FireStatement;
use Blueprint\Models\Statements\RedirectStatement;
use Blueprint\Models\Statements\SendStatement;
use Blueprint\Models\Statements\SessionStatement;

trait MethodsTrait
{
    protected $imports = [];
    protected $data = [];
    protected $statements;
    public $name = '';

    public function __construct($statements, array $data)
    {
        $this->statements = $statements;
        $this->name = data_get($data, 'name', '');
        $this->data = $data;
    }

    public function handle(): array
    {

        $this->imports[$this->name] = data_get($this->data, 'imports', []);
        $this->crudAction();
        data_set($this->data, 'imports', $this->buildImports());
        return $this->data;
    }

    protected function buildImports(): array
    {
        $imports = array_unique($this->imports[$this->name]);
        sort($imports);
        return $imports;
    }

    protected function addImport($class)
    {
        $this->imports[$this->name][] = 'use ' . $class . ';';
    }


    public function sessionOutput($statement): string
    {
        $code = 'session()->' . $statement->operation() . '(';
        $code .= "'" . $statement->reference() . "', ";
        $code .= '$this->model->id';
        $code .= ');';
        return $code;
    }

    private function buildMethods($statements): string
    {
        $body = '';
        foreach ($statements as $statement) {
            if ($statement instanceof SendStatement) {
                $body .= self::INDENT . $statement->output() . PHP_EOL;
                if ($statement->type() === SendStatement::TYPE_NOTIFICATION_WITH_FACADE) {
                    $this->addImport('Illuminate\\Support\\Facades\\Notification');
                    $this->addImport(config('blueprint.namespace') . '\\Notification\\' . $statement->mail());
                } elseif ($statement->type() === SendStatement::TYPE_MAIL) {
                    $this->addImport('Illuminate\\Support\\Facades\\Mail');
                    $this->addImport(config('blueprint.namespace') . '\\Mail\\' . $statement->mail());
                }
            } elseif ($statement instanceof DispatchStatement) {
                $body .= self::INDENT . $statement->output() . PHP_EOL;
                $this->addImport(config('blueprint.namespace') . '\\Jobs\\' . $statement->job());
            } elseif ($statement instanceof FireStatement) {
                $body .= self::INDENT . $statement->output() . PHP_EOL;
                if (!$statement->isNamedEvent()) {
                    $this->addImport(config('blueprint.namespace') . '\\Events\\' . $statement->event());
                }
            } elseif ($statement instanceof RedirectStatement) {
                $this->redirect(self::INDENT.$statement->output().PHP_EOL);
            } elseif ($statement instanceof SessionStatement) {
                $this->session(self::INDENT.$this->sessionOutput($statement).PHP_EOL);
            }
        }

        $body .= $this->session;
        $body .= $this->redirect;

        return trim($body);
    }

}
