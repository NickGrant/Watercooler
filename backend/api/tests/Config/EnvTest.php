<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Config;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Config\Env;

final class EnvTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_ENV['BOOL_FLAG'], $_ENV['INT_FLAG'], $_ENV['EMPTY_VALUE']);
    }

    public function testItReadsBooleanAndIntegerValuesFromEnvironment(): void
    {
        $_ENV['BOOL_FLAG'] = 'true';
        $_ENV['INT_FLAG'] = '8090';

        $env = new Env();

        self::assertTrue($env->getBool('BOOL_FLAG'));
        self::assertSame(8090, $env->getInt('INT_FLAG'));
    }

    public function testItFallsBackWhenValuesAreMissingOrEmpty(): void
    {
        $_ENV['EMPTY_VALUE'] = '';

        $env = new Env();

        self::assertSame('fallback', $env->get('MISSING_VALUE', 'fallback'));
        self::assertSame('fallback', $env->get('EMPTY_VALUE', 'fallback'));
        self::assertFalse($env->getBool('MISSING_BOOL'));
    }
}
