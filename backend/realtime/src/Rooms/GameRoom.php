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

    /**
     * @return ClientSession|null The replaced session if the same player reconnected.
     */
    public function addSession(ClientSession $session): ?ClientSession
    {
        foreach ($this->sessions as $connectionId => $existingSession) {
            if ($existingSession->isSamePlayer($session)) {
                unset($this->sessions[$connectionId]);
                $this->sessions[$session->connectionId] = $session;

                return $existingSession;
            }
        }

        $this->sessions[$session->connectionId] = $session;

        return null;
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
