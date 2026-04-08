<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

interface StartGameRepository
{
    public function findGameBySlug(string $slug): ?GameSummary;

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?StartGamePlayer;

    /**
     * @return list<StartGamePlayer>
     */
    public function listPlayers(int $gameId): array;

    /**
     * @return list<CardSeedDefinition>
     */
    public function listAvailableCards(): array;

    /**
     * @return list<ExecutiveSeedDefinition>
     */
    public function listAvailableExecutives(): array;

    public function persistStartedGame(int $gameId, StartGameSetup $setup): GameSummary;
}
