<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Rooms;

use Watercooler\Realtime\Sessions\ClientSession;

final class ActiveRoomRegistry
{
    /** @var array<string, GameRoom> */
    private array $rooms = [];

    public function getOrCreate(string $slug): GameRoom
    {
        if (!isset($this->rooms[$slug])) {
            $this->rooms[$slug] = new GameRoom($slug);
        }

        return $this->rooms[$slug];
    }

    public function addConnection(string $slug, ClientSession $session): void
    {
        $room = $this->getOrCreate($slug);
        $room->addSession($session);
    }

    public function snapshot(): array
    {
        return array_map(
            static fn(GameRoom $room): array => $room->snapshot(),
            $this->rooms,
        );
    }
}
