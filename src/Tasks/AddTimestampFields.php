<?php

namespace Tanthammar\TallBlueprintAddon\Tasks;

use Closure;
use Tanthammar\TallBlueprintAddon\Contracts\Task;

class AddTimestampFields implements Task
{
    const INDENT = '            ';

    public function handle($data, Closure $next): array
    {
        $model = $data['model'];
        $fields = $data['fields'];
        $imports = $data['imports'];

        $external = config('tall-forms-blueprint.include-external-scripts') ? '->includeExternalScripts()' : null;

        if($model->usesTimestamps() || $model->usesSoftDeletes()) {
            $imports[] = 'DatePicker';
        }

        if ($model->usesTimestamps()) {
            $fields .= self::INDENT . "DatePicker::make('Created at', 'created_at'){$external},";
            $fields .= PHP_EOL;
            $fields .= self::INDENT . "DatePicker::make('Updated at', 'updated_at'){$external},";
        }

        if ($model->usesSoftDeletes()) {
            $fields .= PHP_EOL . self::INDENT . "DatePicker::make('Deleted at', 'deleted_at'){$external},";
        }

        $data['fields'] = $fields;
        $data['imports'] = $imports;

        return $next($data);
    }
}
