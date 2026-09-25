<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\BugReports\BugReportException;
use Watercooler\Api\BugReports\BugReportService;
use CtrlStudio\GameApi\Http\JsonResponse;
use CtrlStudio\GameApi\Http\Request;
use CtrlStudio\GameApi\Http\Response;
use CtrlStudio\GameApi\Http\Routing\RouteMatch;

final class SubmitBugReportAction
{
    public function __construct(
        private readonly BugReportService $bugReportService,
    ) {
    }

    public function __invoke(Request $request, RouteMatch $match): Response
    {
        try {
            $result = $this->bugReportService->submit(
                (string) ($match->params['slug'] ?? ''),
                $request->json,
                $request->headers['user-agent'] ?? null,
            );
        } catch (BugReportException $exception) {
            return match ($exception->statusCode) {
                401 => JsonResponse::unauthorized($exception->toArray()),
                404 => JsonResponse::notFound($exception->toArray()),
                422 => JsonResponse::unprocessable($exception->toArray()),
                default => JsonResponse::badRequest($exception->toArray()),
            };
        }

        return JsonResponse::created($result->toArray());
    }
}
