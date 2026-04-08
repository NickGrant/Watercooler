<?php

declare(strict_types=1);

namespace Watercooler\Api;

use Watercooler\Api\Config\AppConfig;
use Watercooler\Api\Config\Env;
use Watercooler\Api\Database\PdoGameRepository;
use Watercooler\Api\Database\PdoJoinBootstrapRepository;
use Watercooler\Api\Games\CreateGameService;
use Watercooler\Api\Games\OfficeSlugGenerator;
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
use Watercooler\Api\Players\AvatarCatalog;
use Watercooler\Api\Players\JoinBootstrapService;
use Watercooler\Api\Players\SecureSessionTokenGenerator;

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
        $gameRepository = new PdoGameRepository($config->database);
        $joinBootstrapRepository = new PdoJoinBootstrapRepository($config->database);
        $createGameService = new CreateGameService($gameRepository, new OfficeSlugGenerator());
        $joinBootstrapService = new JoinBootstrapService(
            $joinBootstrapRepository,
            new AvatarCatalog(),
            new SecureSessionTokenGenerator(),
        );

        $healthCheckAction = new HealthCheckAction($config);
        $createGameAction = new CreateGameAction($createGameService);
        $getGameAction = new GetGameAction($gameRepository);
        $joinBootstrapAction = new JoinBootstrapAction($joinBootstrapService);
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
        try {
            $request = Request::fromGlobals();
            $match = $this->router->match($request);

            if ($match === null) {
                return JsonResponse::notFound([
                    'error' => 'route_not_found',
                    'message' => 'No API route matched this request.',
                ]);
            }

            return ($match->handler)($request, $match);
        } catch (\Throwable $exception) {
            return JsonResponse::serverError([
                'error' => 'internal_server_error',
                'message' => $this->config->debug
                    ? $exception->getMessage()
                    : 'The API could not process this request.',
            ]);
        }
    }
}
