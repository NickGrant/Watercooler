<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Lobby;

use RuntimeException;

final class RealtimeJoinException extends RuntimeException
{
    public function __construct(
        public readonly string $error,
        string $message,
    ) {
        parent::__construct($message);
    }
}
