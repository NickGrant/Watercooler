<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class EndgameWinner
{
    /**
     * @param list<int> $tiedGamePlayerIds
     */
    public function __construct(
        public readonly int $winnerGamePlayerId,
        public readonly array $tiedGamePlayerIds,
        public readonly int $winningPrestige,
        public readonly int $winningPurchasedCardCount,
    ) {
    }
}
