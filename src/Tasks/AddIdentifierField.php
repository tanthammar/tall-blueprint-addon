<?php

namespace Tanthammar\TallBlueprintAddon\Tasks;

use Blueprint\Models\Column;
use Blueprint\Models\Model;
use Closure;
use Illuminate\Support\Arr;
use Tanthammar\TallBlueprintAddon\Contracts\Task;

class AddIdentifierField implements Task
{
    use InteractWithRelationships;

    const INDENT = '            ';

    public function handle($data, Closure $next): array
    {
        $column = $this->identifierColumn($data['model']);

        $identifierName = $column->name() === 'id' ? '"ID", "id"' : "'".$column->name()."'";
        $data['fields'] .= 'Number::make('.$identifierName.'),'.PHP_EOL.PHP_EOL;
        $data['imports'][] = 'Number';

        return $next($data);
    }

    private function identifierColumn(Model $model): Column
    {
        $name = $this->relationshipIdentifiers($model->columns())
            ->values()
            // filter out all relationships
            ->diff(Arr::get($model->relationships(), 'belongsTo', []))
            ->first();

        return $model->columns()[$name];
    }
}
