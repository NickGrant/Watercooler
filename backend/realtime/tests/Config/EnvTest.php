<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Tests\Config;

use PHPUnit\Framework\TestCase;
use Watercooler\Realtime\Config\Env;

final class EnvTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['REALTIME_PORT'], $_ENV['APP_DEBUG']);
    }

    public function testItParsesConfiguredValues(): void
    {
        $_ENV['REALTIME_PORT'] = '8090';
        $_ENV['APP_DEBUG'] = 'true';

        $env = new Env();

        self::assertSame(8090, $env->getInt('REALTIME_PORT'));
        self::assertTrue($env->getBool('APP_DEBUG'));
    }
}
