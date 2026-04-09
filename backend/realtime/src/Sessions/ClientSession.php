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

    public function isSamePlayer(ClientSession $other): bool
    {
        return $this->gameSlug === $other->gameSlug
            && $this->gamePlayerId !== null
            && $this->gamePlayerId === $other->gamePlayerId;
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
