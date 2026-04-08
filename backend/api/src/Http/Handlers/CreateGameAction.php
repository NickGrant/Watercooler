<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\Games\CreateGameService;
use Watercooler\Api\Http\JsonResponse;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Routing\RouteMatch;

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
