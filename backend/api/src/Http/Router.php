<?php

declare(strict_types=1);

namespace Watercooler\Api\Http;

use Closure;
use Watercooler\Api\Http\Routing\RouteDefinition;
use Watercooler\Api\Http\Routing\RouteMatch;

final class Router
{
    /** @var list<RouteDefinition> */
    private array $routes = [];

    public function get(string $path, Closure $handler): void
    {
        $this->routes[] = new RouteDefinition('GET', $path, $handler);
    }

    public function post(string $path, Closure $handler): void
    {
        $this->routes[] = new RouteDefinition('POST', $path, $handler);
    }

    public function match(Request $request): ?RouteMatch
    {
        foreach ($this->routes as $route) {
            if ($route->method !== $request->method) {
                continue;
            }

            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route->path);
            $pattern = sprintf('#^%s$#', $pattern);

            if (!preg_match($pattern, $request->path, $matches)) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[$key] = $value;
                }
            }

            return new RouteMatch($route->handler, $params);
        }

        return null;
    }
}
