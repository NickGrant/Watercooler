<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use Watercooler\Api\Config\AppConfig;
use Watercooler\Api\Http\JsonResponse;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Response;
use Watercooler\Api\Http\Routing\RouteMatch;

final class HealthCheckAction
{
    public function __construct(
        private readonly AppConfig $config,
    ) {
    }

    public function __invoke(Request $request, RouteMatch $match): Response
    {
        return JsonResponse::ok([
            'status' => 'ok',
            'service' => 'watercooler-api',
            'environment' => $this->config->environment,
            'debug' => $this->config->debug,
        ]);
    }
}
