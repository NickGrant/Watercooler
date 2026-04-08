<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Lobby;

final class LobbyParticipant
{
    public function __construct(
        public readonly int $gameId,
        public readonly int $gamePlayerId,
        public readonly int $playerId,
        public readonly string $gameSlug,
        public readonly string $displayName,
        public readonly bool $isHost,
        public readonly string $joinStatus,
        public readonly AvatarView $avatar,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'gameId' => $this->gameId,
            'gamePlayerId' => $this->gamePlayerId,
            'playerId' => $this->playerId,
            'gameSlug' => $this->gameSlug,
            'displayName' => $this->displayName,
            'isHost' => $this->isHost,
            'joinStatus' => $this->joinStatus,
            'avatar' => $this->avatar->toArray(),
        ];
    }
}
