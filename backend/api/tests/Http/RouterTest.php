<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Router;
use Watercooler\Api\Http\Routing\RouteMatch;

final class RouterTest extends TestCase
{
    public function testItMatchesParameterizedRoutes(): void
    {
        $router = new Router();
        $router->get('/api/games/{slug}', static fn(Request $request, RouteMatch $match): Response => new Response(200, $match->params['slug']));

        $match = $router->match(new Request('GET', '/api/games/synergy-report-telemetry', [], [], null));

        self::assertNotNull($match);
        self::assertSame('synergy-report-telemetry', $match->params['slug']);
    }

    public function testItReturnsNullWhenMethodOrPathDoNotMatch(): void
    {
        $router = new Router();
        $router->get('/health', static fn(Request $request, RouteMatch $match): Response => new Response(200, 'ok'));

        self::assertNull($router->match(new Request('POST', '/health', [], [], null)));
        self::assertNull($router->match(new Request('GET', '/missing', [], [], null)));
    }
}
