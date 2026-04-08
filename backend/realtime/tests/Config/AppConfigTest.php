<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Tests\Config;

use PHPUnit\Framework\TestCase;
use Watercooler\Realtime\Config\AppConfig;
use Watercooler\Realtime\Config\Env;

final class AppConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        unset(
            $_ENV['APP_ENV'],
            $_ENV['APP_DEBUG'],
            $_ENV['REALTIME_HOST'],
            $_ENV['REALTIME_PORT'],
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_NAME'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD'],
        );
    }

    public function testItBuildsDatabaseAndRealtimeSettingsFromEnv(): void
    {
        $_ENV['APP_ENV'] = 'test';
        $_ENV['APP_DEBUG'] = 'false';
        $_ENV['REALTIME_HOST'] = '127.0.0.1';
        $_ENV['REALTIME_PORT'] = '9010';
        $_ENV['DB_HOST'] = 'db';
        $_ENV['DB_PORT'] = '3307';
        $_ENV['DB_NAME'] = 'watercooler_test';
        $_ENV['DB_USER'] = 'tester';
        $_ENV['DB_PASSWORD'] = 'secret';

        $config = AppConfig::fromEnv(new Env());

        self::assertSame('test', $config->environment);
        self::assertFalse($config->debug);
        self::assertSame('127.0.0.1', $config->host);
        self::assertSame(9010, $config->port);
        self::assertSame('db', $config->database->host);
        self::assertSame(3307, $config->database->port);
        self::assertSame('watercooler_test', $config->database->name);
        self::assertSame('tester', $config->database->user);
        self::assertSame('secret', $config->database->password);
    }
}
