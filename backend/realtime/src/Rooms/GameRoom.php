<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Rooms;

use Watercooler\Realtime\Sessions\ClientSession;

final class GameRoom
{
    /** @var array<string, ClientSession> */
    private array $sessions = [];

    public function __construct(
        private readonly string $slug,
    ) {
    }

    public function addSession(ClientSession $session): void
    {
        $this->sessions[$session->connectionId] = $session;
    }

    public function removeSession(string $connectionId): ?ClientSession
    {
        $session = $this->sessions[$connectionId] ?? null;

        if ($session !== null) {
            unset($this->sessions[$connectionId]);
        }

        return $session;
    }

    public function isEmpty(): bool
    {
        return $this->sessions === [];
    }

    public function snapshot(): array
    {
        return [
            'slug' => $this->slug,
            'connections' => array_map(
                static fn(ClientSession $session): array => $session->toArray(),
                array_values($this->sessions),
            ),
        ];
    }
}
