<?php


namespace Tanthammar\TallBlueprintAddon\Tasks;


use Blueprint\Blueprint;
use Blueprint\Models\Controller;
use Blueprint\Models\Statements\DispatchStatement;
use Blueprint\Models\Statements\EloquentStatement;
use Blueprint\Models\Statements\FireStatement;
use Blueprint\Models\Statements\QueryStatement;
use Blueprint\Models\Statements\RedirectStatement;
use Blueprint\Models\Statements\RenderStatement;
use Blueprint\Models\Statements\ResourceStatement;
use Blueprint\Models\Statements\RespondStatement;
use Blueprint\Models\Statements\SendStatement;
use Blueprint\Models\Statements\SessionStatement;
use Blueprint\Models\Statements\ValidateStatement;
use Closure;
use Illuminate\Support\Str;
use Tanthammar\TallBlueprintAddon\Contracts\Task;

class OnCreate implements Task
{
    const INDENT = '        ';
    private $imports = [];

    public function handle(array $data, Closure $next): array
    {
        /** @var Controller $controller */
        $controller = $data['controller'];
        //$create = $data['create'];
        $this->imports[$controller->name()] = $data['imports'];

        $data['create'] = $this->buildMethods($controller);
        $data['imports'] = $this->buildImports($controller);
        return $next($data);
    }

    protected function buildImports(Controller $controller): array
    {
        $imports = array_unique($this->imports[$controller->name()]);
        sort($imports);
        return $imports;
    }

    private function addImport(Controller $controller, $class)
    {
        $this->imports[$controller->name()][] = 'use ' . $class . ';';
    }


    public function fullyQualifyModelReference(string $model_name): string
    {
        $fqn = config('blueprint.namespace');

        if (config('blueprint.models_namespace')) {
            $fqn .= '\\' . config('blueprint.models_namespace');
        }

        $fqn .= '\\' . $model_name;
        return $fqn;
    }

    private function buildMethods(Controller $controller): string
    {
        $body = '';

        foreach ($controller->methods() as $name => $statements) {

            if ($name == 'store') {
                foreach ($statements as $statement) {
                    if ($statement instanceof SendStatement) {
                        $body .= self::INDENT . $statement->output() . PHP_EOL;
                        if ($statement->type() === SendStatement::TYPE_NOTIFICATION_WITH_FACADE) {
                            $this->addImport($controller, 'Illuminate\\Support\\Facades\\Notification');
                            $this->addImport($controller, config('blueprint.namespace') . '\\Notification\\' . $statement->mail());
                        } elseif ($statement->type() === SendStatement::TYPE_MAIL) {
                            $this->addImport($controller, 'Illuminate\\Support\\Facades\\Mail');
                            $this->addImport($controller, config('blueprint.namespace') . '\\Mail\\' . $statement->mail());
                        }
                    } elseif ($statement instanceof DispatchStatement) {
                        $body .= self::INDENT . $statement->output() . PHP_EOL;
                        $this->addImport($controller, config('blueprint.namespace') . '\\Jobs\\' . $statement->job());
                    } elseif ($statement instanceof FireStatement) {
                        $body .= self::INDENT . $statement->output() . PHP_EOL;
                        if (!$statement->isNamedEvent()) {
                            $this->addImport($controller, config('blueprint.namespace') . '\\Events\\' . $statement->event());
                        }
                    } elseif ($statement instanceof RedirectStatement) {
                        $body .= self::INDENT . $statement->output() . PHP_EOL;
                    } elseif ($statement instanceof SessionStatement) {
                        $body .= self::INDENT . str_replace('$request->session()', 'session()', $statement->output()) . PHP_EOL;
                    }

                    $body .= PHP_EOL;
                }

                $body .= PHP_EOL . $body;
            }
        }
        return trim($body);

    }
}
