<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class StartGameSetup
{
    /**
     * @param list<StartGamePlayer> $players
     * @param array<string, int> $bank
     * @param array<int, list<CardSeedDefinition>> $marketCardsByTier
     * @param array<int, list<CardSeedDefinition>> $deckCardsByTier
     * @param list<ExecutiveSeedDefinition> $executives
     */
    public function __construct(
        public readonly array $players,
        public readonly array $bank,
        public readonly array $marketCardsByTier,
        public readonly array $deckCardsByTier,
        public readonly array $executives,
    ) {
    }
}
