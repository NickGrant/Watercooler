<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\Http\JsonResponse;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Routing\RouteMatch;
use Watercooler\Api\Players\JoinBootstrapException;
use Watercooler\Api\Players\JoinBootstrapService;

final class JoinBootstrapAction
{
    public function __construct(
        private readonly JoinBootstrapService $joinBootstrapService,
    ) {
    }

    public function __invoke(Request $request, RouteMatch $match): Response
    {
        try {
            $result = $this->joinBootstrapService->bootstrap(
                (string) ($match->params['slug'] ?? ''),
                $request->json,
            );
        } catch (JoinBootstrapException $exception) {
            return match ($exception->statusCode) {
                401 => JsonResponse::unauthorized($exception->toArray()),
                404 => JsonResponse::notFound($exception->toArray()),
                409 => JsonResponse::conflict($exception->toArray()),
                422 => JsonResponse::unprocessable($exception->toArray()),
                default => JsonResponse::badRequest($exception->toArray()),
            };
        }

        return JsonResponse::ok($result->toArray());
    }
}
