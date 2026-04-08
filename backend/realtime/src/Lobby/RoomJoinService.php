<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Lobby;

use Watercooler\Realtime\Rooms\ActiveRoomRegistry;
use Watercooler\Realtime\Sessions\ClientSession;

final class RoomJoinService
{
    public function __construct(
        private readonly JoinRoomRepository $repository,
        private readonly ActiveRoomRegistry $roomRegistry,
    ) {
    }

    public function join(string $connectionId, string $slug, string $sessionToken): RoomJoinResult
    {
        $participant = $this->repository->findParticipantBySessionToken(
            $slug,
            hash('sha256', $sessionToken),
        );

        if ($participant === null) {
            throw new RealtimeJoinException(
                'invalid_session_token',
                'The realtime service could not validate this temporary session token.',
            );
        }

        $session = new ClientSession(
            connectionId: $connectionId,
            gameSlug: $participant->gameSlug,
            playerId: (string) $participant->playerId,
            gamePlayerId: (string) $participant->gamePlayerId,
        );

        $this->repository->markConnected($participant->gamePlayerId);
        $this->roomRegistry->addConnection($participant->gameSlug, $session);

        return new RoomJoinResult(
            session: $session,
            participant: $participant,
            participants: $this->repository->listParticipants($participant->gameId),
        );
    }

    public function disconnect(string $connectionId): ?ClientSession
    {
        $session = $this->roomRegistry->removeConnection($connectionId);

        if ($session?->gamePlayerId !== null) {
            $this->repository->markDisconnected((int) $session->gamePlayerId);
        }

        return $session;
    }
}
