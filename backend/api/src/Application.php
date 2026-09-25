<?php

declare(strict_types=1);

namespace Watercooler\Api;

use CtrlStudio\GameApi\Config\AppConfig;
use CtrlStudio\GameApi\Config\Env;
use CtrlStudio\GameApi\Config\EnvFile;
use Watercooler\Api\Database\PdoBugReportRepository;
use Watercooler\Api\Database\PdoGameRepository;
use Watercooler\Api\Database\PdoJoinBootstrapRepository;
use Watercooler\Api\Database\PdoStartGameRepository;
use Watercooler\Api\Database\PdoTakeResourcesRepository;
use Watercooler\Api\BugReports\BugReportService;
use Watercooler\Api\Games\ClaimProjectService;
use Watercooler\Api\Games\CreateGameService;
use Watercooler\Api\Games\LoadGameStateService;
use Watercooler\Api\Games\OfficeSlugGenerator;
use Watercooler\Api\Games\PurchaseAdvantageService;
use Watercooler\Api\Games\RandomDeckShuffler;
use Watercooler\Api\Games\StartGameService;
use Watercooler\Api\Games\TakeResourcesService;
use Watercooler\Api\Http\Handlers\CreateGameAction;
use Watercooler\Api\Http\Handlers\SubmitBugReportAction;
use Watercooler\Api\Http\Handlers\ClaimProjectAction;
use Watercooler\Api\Http\Handlers\GameStateAction;
use Watercooler\Api\Http\Handlers\GetGameAction;
use Watercooler\Api\Http\Handlers\HealthCheckAction;
use Watercooler\Api\Http\Handlers\JoinBootstrapAction;
use Watercooler\Api\Http\Handlers\PurchaseAdvantageAction;
use Watercooler\Api\Http\Handlers\StartGameAction;
use Watercooler\Api\Http\Handlers\TakeResourcesAction;
use CtrlStudio\GameApi\Http\CorsPolicy;
use CtrlStudio\GameApi\Http\JsonResponse;
use CtrlStudio\GameApi\Http\Request;
use CtrlStudio\GameApi\Http\Response;
use CtrlStudio\GameApi\Http\Router;
use CtrlStudio\GameApi\Http\Routing\RouteMatch;
use Watercooler\Api\Players\AvatarCatalog;
use Watercooler\Api\Players\JoinBootstrapService;
use Watercooler\Api\Players\SecureSessionTokenGenerator;

final class Application
{
    private function __construct(
        private readonly AppConfig $config,
        private readonly Router $router,
        private readonly CorsPolicy $corsPolicy,
    ) {
    }

    public static function boot(string $basePath): self
    {
        EnvFile::loadIfPresent($basePath . '/.env');
        $env = new Env();
        $config = AppConfig::fromEnv(
            $env,
            defaultDatabaseName: 'watercooler',
            defaultDatabaseUser: 'watercooler',
        );
        $corsPolicy = CorsPolicy::fromEnv($env);
        $router = new Router();
        $gameRepository = new PdoGameRepository($config->database);
        $joinBootstrapRepository = new PdoJoinBootstrapRepository($config->database);
        $startGameRepository = new PdoStartGameRepository($config->database);
        $takeResourcesRepository = new PdoTakeResourcesRepository($config->database);
        $bugReportRepository = new PdoBugReportRepository($config->database);
        $createGameService = new CreateGameService($gameRepository, new OfficeSlugGenerator());
        $bugReportService = new BugReportService(
            $gameRepository,
            $bugReportRepository,
            $bugReportRepository,
        );
        $joinBootstrapService = new JoinBootstrapService(
            $joinBootstrapRepository,
            new AvatarCatalog(),
            new SecureSessionTokenGenerator(),
        );
        $startGameService = new StartGameService(
            $startGameRepository,
            new RandomDeckShuffler(),
        );
        $takeResourcesService = new TakeResourcesService($takeResourcesRepository);
        $claimProjectService = new ClaimProjectService($takeResourcesRepository);
        $purchaseAdvantageService = new PurchaseAdvantageService($takeResourcesRepository);
        $loadGameStateService = new LoadGameStateService($joinBootstrapRepository, $takeResourcesRepository);

        $healthCheckAction = new HealthCheckAction($config);
        $createGameAction = new CreateGameAction($createGameService);
        $submitBugReportAction = new SubmitBugReportAction($bugReportService);
        $claimProjectAction = new ClaimProjectAction($claimProjectService);
        $getGameAction = new GetGameAction($gameRepository);
        $joinBootstrapAction = new JoinBootstrapAction($joinBootstrapService);
        $purchaseAdvantageAction = new PurchaseAdvantageAction($purchaseAdvantageService);
        $startGameAction = new StartGameAction($startGameService);
        $takeResourcesAction = new TakeResourcesAction($takeResourcesService);
        $gameStateAction = new GameStateAction($loadGameStateService);

        $router->get('/health', static fn(Request $request, RouteMatch $match): Response => $healthCheckAction($request, $match));
        $router->post('/api/games', static fn(Request $request, RouteMatch $match): Response => $createGameAction($request, $match));
        $router->post('/api/games/{slug}/bug-reports', static fn(Request $request, RouteMatch $match): Response => $submitBugReportAction($request, $match));
        $router->get('/api/games/{slug}', static fn(Request $request, RouteMatch $match): Response => $getGameAction($request, $match));
        $router->post('/api/games/{slug}/claim-project', static fn(Request $request, RouteMatch $match): Response => $claimProjectAction($request, $match));
        $router->post('/api/games/{slug}/join-bootstrap', static fn(Request $request, RouteMatch $match): Response => $joinBootstrapAction($request, $match));
        $router->post('/api/games/{slug}/purchase-advantage', static fn(Request $request, RouteMatch $match): Response => $purchaseAdvantageAction($request, $match));
        $router->post('/api/games/{slug}/start', static fn(Request $request, RouteMatch $match): Response => $startGameAction($request, $match));
        $router->post('/api/games/{slug}/take-resources', static fn(Request $request, RouteMatch $match): Response => $takeResourcesAction($request, $match));
        $router->get('/api/games/{slug}/state', static fn(Request $request, RouteMatch $match): Response => $gameStateAction($request, $match));

        return new self($config, $router, $corsPolicy);
    }

    public function handle(): Response
    {
        return $this->handleRequest(Request::fromGlobals());
    }

    public function handleRequest(Request $request): Response
    {
        if ($request->isPreflight()) {
            return $this->corsPolicy->preflight($request);
        }

        try {
            $match = $this->router->match($request);

            $response = $match === null
                ? JsonResponse::notFound([
                    'error' => 'route_not_found',
                    'message' => 'No API route matched this request.',
                ])
                : ($match->handler)($request, $match);
        } catch (\Throwable $exception) {
            $response = JsonResponse::serverError([
                'error' => 'internal_server_error',
                'message' => $this->config->debug
                    ? $exception->getMessage()
                    : 'The API could not process this request.',
            ]);
        }

        return $this->corsPolicy->apply($request, $response);
    }
}
