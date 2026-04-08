<?php

declare(strict_types=1);

namespace Watercooler\Api;

use Watercooler\Api\Config\AppConfig;
use Watercooler\Api\Config\Env;
use Watercooler\Api\Http\Handlers\CreateGameAction;
use Watercooler\Api\Http\Handlers\GameStateAction;
use Watercooler\Api\Http\Handlers\GetGameAction;
use Watercooler\Api\Http\Handlers\HealthCheckAction;
use Watercooler\Api\Http\Handlers\JoinBootstrapAction;
use Watercooler\Api\Http\JsonResponse;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Router;
use Watercooler\Api\Http\Routing\RouteMatch;

final class Application
{
    private function __construct(
        private readonly string $basePath,
        private readonly AppConfig $config,
        private readonly Router $router,
    ) {
    }

    public static function boot(string $basePath): self
    {
        $config = AppConfig::fromEnv(new Env());
        $router = new Router();

        $healthCheckAction = new HealthCheckAction($config);
        $createGameAction = new CreateGameAction();
        $getGameAction = new GetGameAction();
        $joinBootstrapAction = new JoinBootstrapAction();
        $gameStateAction = new GameStateAction();

        $router->get('/health', static fn(Request $request, RouteMatch $match): Response => $healthCheckAction($request, $match));
        $router->post('/api/games', static fn(Request $request, RouteMatch $match): Response => $createGameAction($request, $match));
        $router->get('/api/games/{slug}', static fn(Request $request, RouteMatch $match): Response => $getGameAction($request, $match));
        $router->post('/api/games/{slug}/join-bootstrap', static fn(Request $request, RouteMatch $match): Response => $joinBootstrapAction($request, $match));
        $router->get('/api/games/{slug}/state', static fn(Request $request, RouteMatch $match): Response => $gameStateAction($request, $match));

        return new self($basePath, $config, $router);
    }

    public function handle(): Response
    {
        $request = Request::fromGlobals();
        $match = $this->router->match($request);

        if ($match === null) {
            return JsonResponse::notFound([
                'error' => 'route_not_found',
                'message' => 'No API route matched this request.',
            ]);
        }

        return ($match->handler)($request, $match);
    }
}
