<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\Http\JsonResponse;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Routing\RouteMatch;

final class GetGameAction
{
    public function __invoke(Request $request, RouteMatch $match): Response
    {
        return JsonResponse::notImplemented([
            'error' => 'not_implemented',
            'message' => 'Game lookup will be implemented in TASK-015.',
            'route' => 'GET /api/games/{slug}',
            'slug' => $match->params['slug'] ?? null,
        ]);
    }
}
