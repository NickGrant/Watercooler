<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class StartGamePlayer
{
    public function __construct(
        public readonly int $gamePlayerId,
        public readonly string $displayName,
        public readonly bool $isHost,
        public readonly string $joinStatus,
        public readonly ?int $seatOrder = null,
        public readonly int $officePrestige = 0,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'gamePlayerId' => $this->gamePlayerId,
            'displayName' => $this->displayName,
            'isHost' => $this->isHost,
            'joinStatus' => $this->joinStatus,
            'seatOrder' => $this->seatOrder,
            'officePrestige' => $this->officePrestige,
        ];
    }
}
