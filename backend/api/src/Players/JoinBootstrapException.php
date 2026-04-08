<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

use RuntimeException;

final class JoinBootstrapException extends RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $error,
        string $message,
    ) {
        parent::__construct($message);
    }

    /**
     * @return array{error: string, message: string}
     */
    public function toArray(): array
    {
        return [
            'error' => $this->error,
            'message' => $this->getMessage(),
        ];
    }
}
