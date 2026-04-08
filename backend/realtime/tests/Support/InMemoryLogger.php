<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Tests\Support;

use Watercooler\Realtime\Support\Logger;

final class InMemoryLogger implements Logger
{
    /** @var list<array{level: string, message: string, context: array}> */
    public array $entries = [];

    public function info(string $message, array $context = []): void
    {
        $this->entries[] = [
            'level' => 'info',
            'message' => $message,
            'context' => $context,
        ];
    }

    public function debug(string $message, array $context = []): void
    {
        $this->entries[] = [
            'level' => 'debug',
            'message' => $message,
            'context' => $context,
        ];
    }
}
