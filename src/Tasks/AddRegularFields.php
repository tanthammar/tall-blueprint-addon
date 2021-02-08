<?php

namespace Tanthammar\TallBlueprintAddon\Tasks;

use Blueprint\Models\Column;
use Closure;
use Illuminate\Support\Collection;
use Tanthammar\TallBlueprintAddon\Contracts\Task;
use Tanthammar\TallBlueprintAddon\Translators\Rules;

class AddRegularFields implements Task
{
    const INDENT = '            ';
    const INDENT_PLUS = '                ';

    public function handle($data, Closure $next): array
    {
        $model = $data['model'];
        $fields = $data['fields'];
        $imports = $data['imports'];
        $external = config('tall-forms-blueprint.include-external-scripts') ? '->includeExternalScripts()' : null;

        $columns = $this->regularColumns($model->columns());
        foreach ($columns as $column) {
            $fieldType = $this->fieldType($column->dataType());
            $imports[] = $fieldType;

            $field = $fieldType . "::make('" . $this->fieldLabel($column->name()) . "')";
            $field .= $this->addRules($column, $model->tableName());

            if ($column->dataType() === 'json') {
                $field .= PHP_EOL . self::INDENT_PLUS . '->fields([])';
            }

            if (($fieldType === 'Trix' || $fieldType === 'DatePicker') && filled($external)) {
                $field .= PHP_EOL . self::INDENT_PLUS . $external;
            }

            if (in_array($column->dataType(), [
                    'id',
                    'bigincrements',
                    'biginteger',
                    'integer',
                    'unsignedbiginteger',
                    'unsignedinteger',
                    'unsignedmediuminteger',
                    'unsignedsmallinteger',
                    'unsignedtinyinteger',
                ]
            )) {
                $field .= PHP_EOL . self::INDENT_PLUS . '->step(1)->min(1)';
            }

            if (in_array($column->dataType(), [
                    'decimal',
                    'double',
                    'float',
                    'unsigneddecimal',
                ]
            )) {
                $field .= PHP_EOL . self::INDENT_PLUS . '->step(0.10)->min(0.10)';
            }

            $fields .= self::INDENT . $field . ',' . PHP_EOL . PHP_EOL;
        }

        $data['fields'] = $fields;
        $data['imports'] = $imports;

        return $next($data);
    }

    private function regularColumns(array $columns): Collection
    {
        return collect($columns)
            ->filter(function (Column $column) {
                return $column->dataType() !== 'id'
                    && !collect(['id', 'deleted_at', 'created_at', 'updated_at'])->contains($column->name());
            });
    }

    private function fieldLabel($name): string
    {
        return str_replace('_', ' ', ucfirst($name));
    }

    private function addRules(Column $column, string $tableName): string
    {
        if (in_array($column->dataType(), ['id'])) {
            return '';
        }

        $rules = array_map(function ($rule) {
            return " '" . $rule . "'";
        }, Rules::fromColumn($tableName, $column));

        if (empty($rules)) {
            return '';
        }

        return PHP_EOL . self::INDENT_PLUS . '->rules(' . trim(implode(',', $rules)) . ')';
    }

    private function fieldType(string $dataType)
    {
        static $fieldTypes = [
            'id' => 'Number',
            'uuid' => 'Input',
            'bigincrements' => 'Number',
            'biginteger' => 'Number',
            'boolean' => 'Checkbox',
            'date' => 'DatePicker',
            'datetime' => 'DatePicker',
            'datetimetz' => 'DatePicker',
            'decimal' => 'Number',
            'double' => 'Number',
            'float' => 'Number',
            'increments' => 'Number',
            'integer' => 'Number',
            'json' => 'KeyVal',
            'longtext' => 'Trix',
            'mediumincrements' => 'Number',
            'mediuminteger' => 'Number',
            'nullabletimestamps' => 'DatePicker',
            'smallincrements' => 'Number',
            'smallinteger' => 'Number',
            'softdeletes' => 'DatePicker',
            'softdeletestz' => 'DatePicker',
            'time' => 'DatePicker',
            'timetz' => 'DatePicker',
            'timestamp' => 'DatePicker',
            'timestamptz' => 'DatePicker',
            'timestamps' => 'DatePicker',
            'timestampstz' => 'DatePicker',
            'tinyincrements' => 'Number',
            'tinyinteger' => 'Number',
            'unsignedbiginteger' => 'Number',
            'unsigneddecimal' => 'Number',
            'unsignedinteger' => 'Number',
            'unsignedmediuminteger' => 'Number',
            'unsignedsmallinteger' => 'Number',
            'unsignedtinyinteger' => 'Number',
            'year' => 'Number',
        ];

        return $fieldTypes[strtolower($dataType)] ?? 'Input';
    }
}
