<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Sessions;

final class ClientSession
{
    public function __construct(
        public readonly string $connectionId,
        public readonly string $gameSlug,
        public readonly ?string $playerId = null,
        public readonly ?string $gamePlayerId = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'connectionId' => $this->connectionId,
            'gameSlug' => $this->gameSlug,
            'playerId' => $this->playerId,
            'gamePlayerId' => $this->gamePlayerId,
        ];
    }
}
