<?php

namespace Tanthammar\TallBlueprintAddon\Tasks;

use Closure;
use Tanthammar\TallBlueprintAddon\Contracts\Task;

class RemapImports implements Task
{
    public function handle(array $data, Closure $next): array
    {
        $data['imports'] = collect($data['imports'])
            ->unique()
            ->map(function ($type) {
                if(in_array($type, $this->sponsorTypes())) {
                    return 'use Tanthammar\TallFormsSponsors\\'.$type.';';
                } else {
                    return 'use Tanthammar\TallForms\\'.$type.';';
                }
            })
            ->prepend('use Tanthammar\TallForms\TallFormComponent;')
            ->sort(function ($a, $b) {
                return  strlen($a) - strlen($b) ?: strcmp($a, $b);
            })
            ->values()
            ->all();

        return $next($data);
    }

    public function sponsorTypes()
    {
        return [
            'Trix',
            'DatePicker',
            'Number'
        ];
    }
}
