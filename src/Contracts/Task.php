<?php

namespace Tanthammar\TallBlueprintAddon\Contracts;

use Closure;

interface Task
{
    public function handle(array $data, Closure $next): array;
}
