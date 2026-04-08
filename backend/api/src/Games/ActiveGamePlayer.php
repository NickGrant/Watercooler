<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class ActiveGamePlayer
{
    public function __construct(
        public readonly int $gamePlayerId,
        public readonly string $displayName,
        public readonly bool $isHost,
        public readonly string $joinStatus,
        public readonly int $seatOrder,
        public readonly int $officePrestige,
        public readonly PlayerResourceSet $resources,
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
            'resources' => $this->resources->toArray(),
        ];
    }
}
