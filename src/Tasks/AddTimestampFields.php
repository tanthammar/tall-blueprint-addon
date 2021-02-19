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
        $sponsor = config('tall-forms-blueprint.sponsor');
        $external = config('tall-forms-blueprint.include-external-scripts') ? '->includeExternalScripts()' : null;

        if($model->usesTimestamps() || $model->usesSoftDeletes()) {
            $imports[] = $sponsor ? 'DatePicker' : 'Input';
        }

        if ($model->usesTimestamps()) {
            if($sponsor) {
                $fields .= self::INDENT . "DatePicker::make('Created at', 'created_at')->default(now()->toDateTimeLocalString('minute')){$external},";
                $fields .= PHP_EOL;
                $fields .= self::INDENT . "DatePicker::make('Updated at', 'updated_at')->default(now()->toDateTimeLocalString('minute')){$external},";
            } else {
                $fields .= self::INDENT . "Input::make('Created at', 'created_at')->type('datetime-local')->default(now()->toDateTimeLocalString('minute')),";
                $fields .= PHP_EOL;
                $fields .= self::INDENT . "Input::make('Updated at', 'updated_at')->type('datetime-local')->default(now()->toDateTimeLocalString('minute')),";
            }
        }

        if ($model->usesSoftDeletes()) {
            if($sponsor) {
                $fields .= PHP_EOL . self::INDENT . "DatePicker::make('Deleted at', 'deleted_at')->default(now()->toDateTimeLocalString('minute')){$external},";
            } else {
                $fields .= self::INDENT . "Input::make('Deleted at', 'deleted_at')->type('datetime-local')->default(now()->toDateTimeLocalString('minute')),";
            }
        }

        $data['fields'] = $fields;
        $data['imports'] = $imports;

        return $next($data);
    }
}
