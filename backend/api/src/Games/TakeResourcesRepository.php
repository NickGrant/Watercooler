<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

interface TakeResourcesRepository
{
    public function findGameBySlug(string $slug): ?GameSummary;

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?StartGamePlayer;

    public function loadState(int $gameId): ActiveGameState;

    /**
     * @param list<string> $resources
     */
    public function applyTakeResources(
        int $gameId,
        int $actingGamePlayerId,
        array $resources,
        ActiveGameState $state,
    ): ActiveGameState;
}
