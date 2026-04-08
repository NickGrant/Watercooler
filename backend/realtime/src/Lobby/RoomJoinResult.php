<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Lobby;

use Watercooler\Realtime\Sessions\ClientSession;

final class RoomJoinResult
{
    /**
     * @param list<LobbyParticipant> $participants
     */
    public function __construct(
        public readonly ClientSession $session,
        public readonly LobbyParticipant $participant,
        public readonly array $participants,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'session' => $this->session->toArray(),
            'participant' => $this->participant->toArray(),
            'presence' => array_map(
                static fn(LobbyParticipant $participant): array => $participant->toArray(),
                $this->participants,
            ),
        ];
    }
}
