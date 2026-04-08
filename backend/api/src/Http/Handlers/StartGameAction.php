<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\Games\StartGameException;
use Watercooler\Api\Games\StartGameService;
use Watercooler\Api\Http\JsonResponse;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Routing\RouteMatch;

final class StartGameAction
{
    public function __construct(
        private readonly StartGameService $startGameService,
    ) {
    }

    public function __invoke(Request $request, RouteMatch $match): Response
    {
        try {
            $result = $this->startGameService->start(
                (string) ($match->params['slug'] ?? ''),
                $request->json,
            );
        } catch (StartGameException $exception) {
            return match ($exception->statusCode) {
                401 => JsonResponse::unauthorized($exception->toArray()),
                403 => new JsonResponse(403, $exception->toArray()),
                404 => JsonResponse::notFound($exception->toArray()),
                409 => JsonResponse::conflict($exception->toArray()),
                default => JsonResponse::badRequest($exception->toArray()),
            };
        }

        return JsonResponse::ok($result->toArray());
    }
}
