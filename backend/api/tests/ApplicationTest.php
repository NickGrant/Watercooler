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

    public function testItReturnsNotFoundForUnknownRoutes(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/missing';

        $response = Application::boot(dirname(__DIR__))->handle();

        self::assertSame(404, $response->statusCode);
        self::assertStringContainsString('route_not_found', $response->body);
    }

    public function testItReturnsJsonWhenPersistenceFails(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/api/games';
        $_ENV['DB_HOST'] = 'invalid-host-name';
        $_ENV['DB_NAME'] = 'missing';
        $_ENV['DB_USER'] = 'missing';
        $_ENV['DB_PASSWORD'] = 'missing';

        $response = Application::boot(dirname(__DIR__))->handle();

        self::assertSame(500, $response->statusCode);
        self::assertStringContainsString('internal_server_error', $response->body);
    }
}
