<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class TakeResourcesException extends \RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $error,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'error' => $this->error,
            'message' => $this->getMessage(),
        ];
    }
}
