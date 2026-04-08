<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Application;

final class ApplicationTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
        $_SERVER = [];
        $_ENV = [];
    }

    public function testItServesTheHealthRoute(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/health';

        $response = Application::boot(dirname(__DIR__))->handle();

        self::assertSame(200, $response->statusCode);
        self::assertStringContainsString('"service": "watercooler-api"', $response->body);
    }

    public function testItReturnsNotImplementedForPlannedGameEndpoints(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/games/synergy-report-telemetry';

        $response = Application::boot(dirname(__DIR__))->handle();

        self::assertSame(501, $response->statusCode);
        self::assertStringContainsString('"slug": "synergy-report-telemetry"', $response->body);
    }

    public function testItReturnsNotFoundForUnknownRoutes(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/missing';

        $response = Application::boot(dirname(__DIR__))->handle();

        self::assertSame(404, $response->statusCode);
        self::assertStringContainsString('route_not_found', $response->body);
    }
}
