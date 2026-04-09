<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\Games\LoadGameStateException;
use Watercooler\Api\Games\LoadGameStateService;
use Watercooler\Api\Http\JsonResponse;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Routing\RouteMatch;

final class GameStateAction
{
    public function __construct(
        private readonly LoadGameStateService $loadGameStateService,
    ) {
    }

    public function __invoke(Request $request, RouteMatch $match): Response
    {
        try {
            $result = $this->loadGameStateService->load(
                (string) ($match->params['slug'] ?? ''),
                (string) ($request->query['sessionToken'] ?? $request->headers['x-session-token'] ?? ''),
            );
        } catch (LoadGameStateException $exception) {
            return match ($exception->statusCode) {
                401 => JsonResponse::unauthorized($exception->toArray()),
                404 => JsonResponse::notFound($exception->toArray()),
                default => JsonResponse::badRequest($exception->toArray()),
            };
        }

        return JsonResponse::ok($result->toArray());
    }
}
