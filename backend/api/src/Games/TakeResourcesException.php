<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class TakeResourcesException extends \RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $error,
        string $message,
        public readonly ?array $recovery = null,
    ) {
        parent::__construct($message);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'error' => $this->error,
            'message' => $this->getMessage(),
        ];

        if ($this->recovery !== null) {
            $payload['recovery'] = $this->recovery;
        }

        return $payload;
    }
}
