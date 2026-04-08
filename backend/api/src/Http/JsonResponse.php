<?php

declare(strict_types=1);

namespace Watercooler\Api\Http;

final class JsonResponse extends Response
{
    /**
     * @param array<string, mixed> $data
     */
    public static function ok(array $data): self
    {
        return new self(200, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function accepted(array $data): self
    {
        return new self(202, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function notFound(array $data): self
    {
        return new self(404, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function notImplemented(array $data): self
    {
        return new self(501, $data);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(int $statusCode, array $payload, array $headers = [])
    {
        parent::__construct(
            $statusCode,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}',
            ['Content-Type' => 'application/json; charset=utf-8', ...$headers],
        );
    }
}
