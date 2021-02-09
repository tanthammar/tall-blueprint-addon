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
        $update = $data['update'];
        $this->imports[$controller->name()] = $data['imports'];

        $data['update'] = $this->buildMethods($controller, $update);
        $data['imports'] = $this->buildImports($controller);
        echo($data['imports']);
        return $next($data);
    }

    protected function buildImports(Controller $controller): string
    {
        $imports = array_unique($this->imports[$controller->name()]);
        sort($imports);

        return implode(PHP_EOL, array_map(function ($class) {
            return 'use '.$class.';';
        }, $imports));
    }

    private function addImport(Controller $controller, $class)
    {
        $this->imports[$controller->name()][] = $class;
    }

    private function determineModel(Controller $controller, ?string $reference): string
    {
        if (empty($reference) || $reference === 'id') {
            return $this->fullyQualifyModelReference(Str::studly(Str::singular($controller->prefix())));
        }

        if (Str::contains($reference, '.')) {
            return $this->fullyQualifyModelReference(Str::studly(Str::before($reference, '.')));
        }

        return $this->fullyQualifyModelReference(Str::studly($reference));
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

    private function buildMethods(Controller $controller, $update): string
    {
        $body = $update;

        foreach ($controller->methods() as $name => $statements) {

            echo $name . PHP_EOL;
            if ($name == 'update') {

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
                    } elseif ($statement instanceof RenderStatement) {
                        $body .= self::INDENT . $statement->output() . PHP_EOL;
                    } elseif ($statement instanceof ResourceStatement) {
                        $fqcn = config('blueprint.namespace') . '\\Http\\Resources\\' . ($controller->namespace() ? $controller->namespace() . '\\' : '') . $statement->name();
                        $this->addImport($controller, $fqcn);
                        $body .= self::INDENT . $statement->output() . PHP_EOL;
                    } elseif ($statement instanceof RedirectStatement) {
                        $body .= self::INDENT . $statement->output() . PHP_EOL;
                    } elseif ($statement instanceof RespondStatement) {
                        $body .= self::INDENT . $statement->output() . PHP_EOL;
                    } elseif ($statement instanceof SessionStatement) {
                        $body .= self::INDENT . $statement->output() . PHP_EOL;
                    } elseif ($statement instanceof EloquentStatement) {
                        $body .= self::INDENT . $statement->output($controller->prefix(), $name, false) . PHP_EOL;
                        $this->addImport($controller, $this->determineModel($controller, $statement->reference()));
                    } elseif ($statement instanceof QueryStatement) {
                        $body .= self::INDENT . $statement->output($controller->prefix()) . PHP_EOL;
                        $this->addImport($controller, $this->determineModel($controller, $statement->model()));
                    }

                    $body .= PHP_EOL;
                }

                $body .= PHP_EOL . $body;
            }
        }

        echo trim($body);

        return trim($body);

    }
}
