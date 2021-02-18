<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;

use Blueprint\Models\Statements\DispatchStatement;
use Blueprint\Models\Statements\EloquentStatement;
use Blueprint\Models\Statements\FireStatement;
use Blueprint\Models\Statements\QueryStatement;
use Blueprint\Models\Statements\RedirectStatement;
use Blueprint\Models\Statements\SendStatement;
use Blueprint\Models\Statements\SessionStatement;

trait MethodsTrait
{
    protected $imports = [];
    protected $data = [];
    protected $statements;
    protected $name = '';
    protected $action = '';
    protected $eloquentActions = [
        'create' => '$this->model = DummyModel::create($validated_data);' . PHP_EOL . self::INDENT . '$dummymodel = $this->model;' . PHP_EOL . self::INDENT . '$this->showDelete = true;',
        'update' => '$this->model->update($validated_data);' . PHP_EOL . self::INDENT . '$dummymodel = $this->model;',
        'delete' => '$this->model->delete();'
    ];

    public function __construct($statements, array $data)
    {
        $this->statements = $statements;
        $this->name = data_get($data, 'name', ''); //$controller->name()
        $this->action = data_get($data, 'action', '');//create, update, delete
        $this->data = $data;
    }

    public function handle(): array
    {

        $this->imports[$this->name] = data_get($this->data, 'imports', []);
        data_set($this->data, $this->action, $this->buildMethods($this->statements));
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

    protected function makeEloquentStatement(string $statement): string
    {
        $string = \Str::contains($statement, $this->action . '(') ? $this->eloquentActions[$this->action] : $statement;
        if (\Str::contains($string, '->save(')) $string = $this->eloquentActions['create'];
        return $string;
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
                $this->redirect(self::INDENT . $statement->output() . PHP_EOL);
                $body .= $this->redirect;
            } elseif ($statement instanceof SessionStatement) {
                $this->session(self::INDENT . str_replace('$request->', null, $statement->output()) . PHP_EOL);
                $body .= $this->session;
            } elseif ($statement instanceof EloquentStatement) {
                $body .= self::INDENT . $this->makeEloquentStatement($statement->output($this->name, $this->action, false)) . PHP_EOL;
            } elseif ($statement instanceof QueryStatement) {
                $body .= self::INDENT . '//Otiose QueryStatement from Blueprint' . PHP_EOL;
                $body .= self::INDENT . '//' . $statement->output($this->name) . PHP_EOL;
            }
        }

//        $body .= $this->session;
//        $body .= $this->redirect;

        return trim($body);
    }

}
