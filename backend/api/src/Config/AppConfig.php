<?php

declare(strict_types=1);

namespace Watercooler\Api\Config;

final class AppConfig
{
    public function __construct(
        public readonly string $environment,
        public readonly bool $debug,
        public readonly string $appUrl,
        public readonly DatabaseConfig $database,
    ) {
    }

    public static function fromEnv(Env $env): self
    {
        return new self(
            environment: $env->get('APP_ENV', 'development'),
            debug: $env->getBool('APP_DEBUG', true),
            appUrl: $env->get('APP_URL', 'http://localhost:8080'),
            database: new DatabaseConfig(
                host: $env->get('DB_HOST', '127.0.0.1'),
                port: $env->getInt('DB_PORT', 3306),
                name: $env->get('DB_NAME', 'watercooler'),
                user: $env->get('DB_USER', 'watercooler'),
                password: $env->get('DB_PASSWORD', 'watercooler'),
            ),
        );
    }
}
