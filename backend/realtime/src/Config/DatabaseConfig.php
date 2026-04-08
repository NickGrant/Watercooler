<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Config;

final class DatabaseConfig
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $name,
        public readonly string $user,
        public readonly string $password,
    ) {
    }

    public static function fromEnv(Env $env): self
    {
        return new self(
            host: $env->get('DB_HOST', '127.0.0.1') ?? '127.0.0.1',
            port: $env->getInt('DB_PORT', 3306),
            name: $env->get('DB_NAME', 'watercooler') ?? 'watercooler',
            user: $env->get('DB_USER', 'root') ?? 'root',
            password: $env->get('DB_PASSWORD', '') ?? '',
        );
    }
}
