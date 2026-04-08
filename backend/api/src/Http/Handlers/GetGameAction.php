<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\Games\GameRepository;
use Watercooler\Api\Http\JsonResponse;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Routing\RouteMatch;

final class GetGameAction
{
    public function __construct(
        private readonly GameRepository $gameRepository,
    ) {
    }

    public function __invoke(Request $request, RouteMatch $match): Response
    {
        $slug = $match->params['slug'] ?? '';
        $game = $this->gameRepository->findBySlug($slug);

        if ($game === null) {
            return JsonResponse::notFound([
                'error' => 'game_not_found',
                'message' => 'No game exists for the provided slug.',
                'slug' => $slug,
            ]);
        }

        return JsonResponse::ok([
            'game' => $game->toArray(),
        ]);
    }
}
