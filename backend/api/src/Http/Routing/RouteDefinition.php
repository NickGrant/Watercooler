<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Routing;

use Closure;

final class RouteDefinition
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly Closure $handler,
    ) {
    }
}
