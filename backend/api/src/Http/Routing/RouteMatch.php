<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Routing;

use Closure;

final class RouteMatch
{
    /**
     * @param array<string, string> $params
     */
    public function __construct(
        public readonly Closure $handler,
        public readonly array $params,
    ) {
    }
}
