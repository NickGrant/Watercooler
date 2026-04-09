<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Tests\Lobby;

use PHPUnit\Framework\TestCase;
use Watercooler\Realtime\Lobby\AvatarView;
use Watercooler\Realtime\Lobby\JoinRoomRepository;
use Watercooler\Realtime\Lobby\LobbyParticipant;
use Watercooler\Realtime\Lobby\RealtimeJoinException;
use Watercooler\Realtime\Lobby\RoomJoinService;
use Watercooler\Realtime\Rooms\ActiveRoomRegistry;

final class RoomJoinServiceTest extends TestCase
{
    public function testItAuthenticatesAConnectionAndBuildsPresenceState(): void
    {
        $repository = new InMemoryJoinRoomRepository();
        $service = new RoomJoinService($repository, new ActiveRoomRegistry());

        $result = $service->join('conn-1', 'synergy-report-telemetry', 'temporary-session-token');

        self::assertSame('conn-1', $result->session->connectionId);
        self::assertSame('connected', $repository->participants[1]->joinStatus);
        self::assertCount(2, $result->participants);
        self::assertFalse($result->wasReconnect);
    }

    public function testItFlagsAReconnectWhenTheSamePlayerJoinsFromADifferentConnection(): void
    {
        $repository = new InMemoryJoinRoomRepository();
        $registry = new ActiveRoomRegistry();
        $service = new RoomJoinService($repository, $registry);

        $service->join('conn-1', 'synergy-report-telemetry', 'temporary-session-token');
        $result = $service->join('conn-2', 'synergy-report-telemetry', 'temporary-session-token');

        self::assertTrue($result->wasReconnect);
        self::assertSame('conn-1', $result->replacedConnectionId);
        self::assertSame('conn-2', $registry->snapshot()['synergy-report-telemetry']['connections'][0]['connectionId']);
    }

    public function testItRejectsUnknownTokens(): void
    {
        $service = new RoomJoinService(new InMemoryJoinRoomRepository(), new ActiveRoomRegistry());

        $this->expectException(RealtimeJoinException::class);
        $this->expectExceptionMessage('The realtime service could not validate this temporary session token.');

        $service->join('conn-1', 'synergy-report-telemetry', 'wrong-token');
    }

    public function testItMarksPlayersDisconnectedWhenConnectionsClose(): void
    {
        $repository = new InMemoryJoinRoomRepository();
        $service = new RoomJoinService($repository, new ActiveRoomRegistry());

        $service->join('conn-1', 'synergy-report-telemetry', 'temporary-session-token');
        $service->disconnect('conn-1');

        self::assertSame('disconnected', $repository->participants[1]->joinStatus);
    }

    public function testItIgnoresDisconnectsForUnknownConnections(): void
    {
        $repository = new InMemoryJoinRoomRepository();
        $service = new RoomJoinService($repository, new ActiveRoomRegistry());

        self::assertNull($service->disconnect('missing-connection'));
        self::assertSame('joined', $repository->participants[1]->joinStatus);
    }
}

final class InMemoryJoinRoomRepository implements JoinRoomRepository
{
    /** @var array<int, LobbyParticipant> */
    public array $participants;

    /** @var array<string, int> */
    private array $tokenMap;

    public function __construct()
    {
        $this->participants = [
            1 => new LobbyParticipant(
                gameId: 1,
                gamePlayerId: 1,
                playerId: 101,
                gameSlug: 'synergy-report-telemetry',
                displayName: 'Pam',
                isHost: true,
                joinStatus: 'joined',
                avatar: new AvatarView('blazer', 'corporate-neutral', 'side-part'),
            ),
            2 => new LobbyParticipant(
                gameId: 1,
                gamePlayerId: 2,
                playerId: 102,
                gameSlug: 'synergy-report-telemetry',
                displayName: 'Jim',
                isHost: false,
                joinStatus: 'joined',
                avatar: new AvatarView('hoodie', 'coffee-grin', 'startup-mess'),
            ),
        ];
        $this->tokenMap = [
            hash('sha256', 'temporary-session-token') => 1,
            hash('sha256', 'second-token') => 2,
        ];
    }

    public function findParticipantBySessionToken(string $slug, string $sessionTokenHash): ?LobbyParticipant
    {
        $gamePlayerId = $this->tokenMap[$sessionTokenHash] ?? null;

        if ($gamePlayerId === null) {
            return null;
        }

        $participant = $this->participants[$gamePlayerId] ?? null;

        return $participant !== null && $participant->gameSlug === $slug ? $participant : null;
    }

    public function listParticipants(int $gameId): array
    {
        return array_values(
            array_filter(
                $this->participants,
                static fn(LobbyParticipant $participant): bool => $participant->gameId === $gameId,
            ),
        );
    }

    public function markConnected(int $gamePlayerId): void
    {
        $participant = $this->participants[$gamePlayerId];
        $this->participants[$gamePlayerId] = new LobbyParticipant(
            gameId: $participant->gameId,
            gamePlayerId: $participant->gamePlayerId,
            playerId: $participant->playerId,
            gameSlug: $participant->gameSlug,
            displayName: $participant->displayName,
            isHost: $participant->isHost,
            joinStatus: 'connected',
            avatar: $participant->avatar,
        );
    }

    public function markDisconnected(int $gamePlayerId): void
    {
        $participant = $this->participants[$gamePlayerId];
        $this->participants[$gamePlayerId] = new LobbyParticipant(
            gameId: $participant->gameId,
            gamePlayerId: $participant->gamePlayerId,
            playerId: $participant->playerId,
            gameSlug: $participant->gameSlug,
            displayName: $participant->displayName,
            isHost: $participant->isHost,
            joinStatus: 'disconnected',
            avatar: $participant->avatar,
        );
    }
}
