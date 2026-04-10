<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

use Watercooler\Api\Games\GameSummary;

final class JoinBootstrapResult
{
    /**
     * @param list<JoinedPlayer> $joinedPlayers
     */
    public function __construct(
        public readonly GameSummary $game,
        public readonly JoinedPlayer $player,
        public readonly string $sessionToken,
        public readonly array $joinedPlayers,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'game' => $this->game->toArray(),
            'player' => $this->player->toArray(),
            'session' => [
                'token' => $this->sessionToken,
                'reconnectEnabled' => true,
            ],
            'lobby' => [
                'minimumPlayers' => 2,
                'maximumPlayers' => 4,
                'canStart' => count($this->joinedPlayers) >= 2 && $this->player->isHost,
                'joinedPlayers' => array_map(
                    static fn(JoinedPlayer $joinedPlayer): array => $joinedPlayer->toArray(),
                    $this->joinedPlayers,
                ),
            ],
            'realtime' => [
                'transport' => 'polling',
                'roomSlug' => $this->game->slug,
                'sessionToken' => $this->sessionToken,
            ],
        ];
    }
}
