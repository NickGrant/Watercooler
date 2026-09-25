<?php

declare(strict_types=1);

namespace Watercooler\Api\Http\Handlers;

use CtrlStudio\GameApi\Config\AppConfig;
use CtrlStudio\GameApi\Http\JsonResponse;
use CtrlStudio\GameApi\Http\Request;
use CtrlStudio\GameApi\Http\Response;
use CtrlStudio\GameApi\Http\Routing\RouteMatch;

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
