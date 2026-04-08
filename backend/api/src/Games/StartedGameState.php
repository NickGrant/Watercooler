<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class StartedGameState
{
    /**
     * @param list<StartGamePlayer> $players
     * @param array<string, int> $bank
     * @param array<int, list<CardSeedDefinition>> $marketCardsByTier
     * @param list<ExecutiveSeedDefinition> $executives
     */
    public function __construct(
        public readonly array $players,
        public readonly int $currentTurnGamePlayerId,
        public readonly array $bank,
        public readonly array $marketCardsByTier,
        public readonly array $executives,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'currentTurnGamePlayerId' => $this->currentTurnGamePlayerId,
            'players' => array_map(
                static fn(StartGamePlayer $player): array => $player->toArray(),
                $this->players,
            ),
            'bank' => $this->bank,
            'market' => [
                'tier1' => array_map(
                    static fn(CardSeedDefinition $card, int $index): array => $card->toArray($index + 1),
                    $this->marketCardsByTier[1] ?? [],
                    array_keys($this->marketCardsByTier[1] ?? []),
                ),
                'tier2' => array_map(
                    static fn(CardSeedDefinition $card, int $index): array => $card->toArray($index + 1),
                    $this->marketCardsByTier[2] ?? [],
                    array_keys($this->marketCardsByTier[2] ?? []),
                ),
                'tier3' => array_map(
                    static fn(CardSeedDefinition $card, int $index): array => $card->toArray($index + 1),
                    $this->marketCardsByTier[3] ?? [],
                    array_keys($this->marketCardsByTier[3] ?? []),
                ),
            ],
            'executives' => array_map(
                static fn(ExecutiveSeedDefinition $executive, int $index): array => $executive->toArray($index + 1),
                $this->executives,
                array_keys($this->executives),
            ),
        ];
    }
}
