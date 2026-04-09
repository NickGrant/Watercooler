<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

interface ClaimProjectRepository
{
    public function findGameBySlug(string $slug): ?GameSummary;

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?StartGamePlayer;

    public function loadState(int $gameId): ActiveGameState;

    public function applyClaimProject(
        int $gameId,
        int $actingGamePlayerId,
        string $source,
        int $tier,
        ?int $marketSlot,
        ActiveGameState $state,
    ): ActiveGameState;
}
