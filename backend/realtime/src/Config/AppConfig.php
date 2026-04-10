<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Config;

final class AppConfig
{
    public function __construct(
        public readonly string $environment,
        public readonly bool $debug,
        public readonly string $host,
        public readonly int $port,
        public readonly string $apiBaseUrl,
        public readonly int $syncIntervalMs,
        public readonly DatabaseConfig $database,
    ) {
    }

    public static function fromEnv(Env $env): self
    {
        return new self(
            environment: $env->get('APP_ENV', 'development'),
            debug: $env->getBool('APP_DEBUG', true),
            host: $env->get('REALTIME_HOST', '0.0.0.0'),
            port: $env->getInt('REALTIME_PORT', 8090),
            apiBaseUrl: rtrim($env->get('API_BASE_URL', 'http://api:8080'), '/'),
            syncIntervalMs: $env->getInt('REALTIME_SYNC_INTERVAL_MS', 1000),
            database: DatabaseConfig::fromEnv($env),
        );
    }
}
