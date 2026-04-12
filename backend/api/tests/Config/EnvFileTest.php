<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Config;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Config\EnvFile;

final class EnvFileTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = sys_get_temp_dir() . '/watercooler-envfile-test.env';
        @unlink($this->path);
        putenv('WC_ENVFILE_SAMPLE');
        unset($_ENV['WC_ENVFILE_SAMPLE'], $_SERVER['WC_ENVFILE_SAMPLE']);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
        putenv('WC_ENVFILE_SAMPLE');
        unset($_ENV['WC_ENVFILE_SAMPLE'], $_SERVER['WC_ENVFILE_SAMPLE']);
        parent::tearDown();
    }

    public function testItLoadsAValueFromDotEnvWhenMissingFromRuntimeEnv(): void
    {
        file_put_contents($this->path, "WC_ENVFILE_SAMPLE=loaded-from-file\n");

        EnvFile::loadIfPresent($this->path);

        self::assertSame('loaded-from-file', $_ENV['WC_ENVFILE_SAMPLE']);
        self::assertSame('loaded-from-file', $_SERVER['WC_ENVFILE_SAMPLE']);
        self::assertSame('loaded-from-file', getenv('WC_ENVFILE_SAMPLE'));
    }

    public function testItDoesNotOverrideExistingRuntimeValues(): void
    {
        $_ENV['WC_ENVFILE_SAMPLE'] = 'existing-value';
        file_put_contents($this->path, "WC_ENVFILE_SAMPLE=loaded-from-file\n");

        EnvFile::loadIfPresent($this->path);

        self::assertSame('existing-value', $_ENV['WC_ENVFILE_SAMPLE']);
    }
}
