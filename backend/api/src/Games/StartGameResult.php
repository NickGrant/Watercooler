<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class StartGameResult
{
    public function __construct(
        public readonly GameSummary $game,
        public readonly StartedGameState $state,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'game' => $this->game->toArray(),
            'state' => $this->state->toArray(),
        ];
    }
}
