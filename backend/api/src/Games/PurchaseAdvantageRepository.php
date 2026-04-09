<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

interface PurchaseAdvantageRepository
{
    public function findGameBySlug(string $slug): ?GameSummary;

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?StartGamePlayer;

    public function loadState(int $gameId): ActiveGameState;

    /**
     * @param array<string, int> $spentResources
     */
    public function applyPurchaseAdvantage(
        int $gameId,
        int $actingGamePlayerId,
        string $source,
        ?int $tier,
        ?int $marketSlot,
        ?string $cardCode,
        array $spentResources,
        ActiveGameState $state,
    ): ActiveGameState;
}
