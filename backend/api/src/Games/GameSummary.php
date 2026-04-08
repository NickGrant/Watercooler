<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class GameSummary
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $status,
        public readonly string $phase,
        public readonly int $playerCount,
        public readonly string $createdAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'status' => $this->status,
            'phase' => $this->phase,
            'playerCount' => $this->playerCount,
            'createdAt' => $this->createdAt,
            'path' => sprintf('/game/%s', $this->slug),
        ];
    }
}
