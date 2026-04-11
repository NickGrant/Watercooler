<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

use Watercooler\Api\Players\JoinedPlayer;

final class LoadGameStateResult
{
    /**
     * @param list<JoinedPlayer>|null $joinedPlayers
     */
    public function __construct(
        public readonly GameSummary $game,
        public readonly JoinedPlayer $player,
        public readonly string $sessionToken,
        public readonly ?array $joinedPlayers = null,
        public readonly ?ActiveGameState $state = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'game' => $this->game->toArray(),
            'player' => $this->player->toArray(),
            'session' => [
                'token' => $this->sessionToken,
                'reconnectEnabled' => true,
            ],
            'transport' => [
                'transport' => 'polling',
                'roomSlug' => $this->game->slug,
                'sessionToken' => $this->sessionToken,
            ],
        ];

        if ($this->joinedPlayers !== null) {
            $payload['lobby'] = [
                'minimumPlayers' => 2,
                'maximumPlayers' => 4,
                'canStart' => count($this->joinedPlayers) >= 2 && $this->player->isHost,
                'joinedPlayers' => array_map(
                    static fn(JoinedPlayer $joinedPlayer): array => $joinedPlayer->toArray(),
                    $this->joinedPlayers,
                ),
            ];
        }

        if ($this->state !== null) {
            $payload['state'] = $this->state->toArray();
        }

        return $payload;
    }
}
