<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Lobby;

interface JoinRoomRepository
{
    public function findParticipantBySessionToken(string $slug, string $sessionTokenHash): ?LobbyParticipant;

    /**
     * @return list<LobbyParticipant>
     */
    public function listParticipants(int $gameId): array;

    public function markConnected(int $gamePlayerId): void;

    public function markDisconnected(int $gamePlayerId): void;
}
