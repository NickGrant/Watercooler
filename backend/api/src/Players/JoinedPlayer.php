<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

final class JoinedPlayer
{
    public function __construct(
        public readonly int $gamePlayerId,
        public readonly int $playerId,
        public readonly string $displayName,
        public readonly bool $isHost,
        public readonly string $joinStatus,
        public readonly AvatarSelection $avatar,
    ) {
    }

    /**
     * @return array{
     *     gamePlayerId: int,
     *     playerId: int,
     *     displayName: string,
     *     isHost: bool,
     *     joinStatus: string,
     *     avatar: array{body: string, face: string, hair: string}
     * }
     */
    public function toArray(): array
    {
        return [
            'gamePlayerId' => $this->gamePlayerId,
            'playerId' => $this->playerId,
            'displayName' => $this->displayName,
            'isHost' => $this->isHost,
            'joinStatus' => $this->joinStatus,
            'avatar' => $this->avatar->toArray(),
        ];
    }
}
