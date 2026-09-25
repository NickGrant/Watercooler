<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\Games\CreateGameService;
use CtrlStudio\GameApi\Http\JsonResponse;
use CtrlStudio\GameApi\Http\Request;
use CtrlStudio\GameApi\Http\Response;
use CtrlStudio\GameApi\Http\Routing\RouteMatch;

final class CreateGameAction
{
    public function __construct(
        private readonly CreateGameService $createGameService,
    ) {
    }

    public function __invoke(Request $request, RouteMatch $match): Response
    {
        $game = $this->createGameService->createGame();

        return JsonResponse::created([
            'game' => $game->toArray(),
        ]);
    }
}
