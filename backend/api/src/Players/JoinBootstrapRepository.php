<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

use Watercooler\Api\Games\GameSummary;

interface JoinBootstrapRepository
{
    public function findGameBySlug(string $slug): ?GameSummary;

    public function displayNameExists(int $gameId, string $displayName): bool;

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?JoinedPlayer;

    public function createJoinedPlayer(
        int $gameId,
        string $displayName,
        AvatarSelection $avatar,
        string $sessionTokenHash,
    ): JoinedPlayer;

    /**
     * @return list<JoinedPlayer>
     */
    public function listPlayers(int $gameId): array;
}
