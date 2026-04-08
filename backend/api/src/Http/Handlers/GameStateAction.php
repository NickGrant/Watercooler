<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\Http\JsonResponse;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Routing\RouteMatch;

final class GameStateAction
{
    public function __invoke(Request $request, RouteMatch $match): Response
    {
        return JsonResponse::notImplemented([
            'error' => 'not_implemented',
            'message' => 'Game state reads will be implemented in a later task.',
            'route' => 'GET /api/games/{slug}/state',
            'slug' => $match->params['slug'] ?? null,
        ]);
    }
}
