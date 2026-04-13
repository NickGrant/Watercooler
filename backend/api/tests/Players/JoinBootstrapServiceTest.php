<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Players;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Players\AvatarCatalog;
use Watercooler\Api\Players\AvatarSelection;
use Watercooler\Api\Players\JoinBootstrapException;
use Watercooler\Api\Players\JoinBootstrapRepository;
use Watercooler\Api\Players\JoinBootstrapService;
use Watercooler\Api\Players\JoinedPlayer;
use Watercooler\Api\Players\SessionTokenGenerator;

final class JoinBootstrapServiceTest extends TestCase
{
    public function testItCreatesAJoinedPlayerAndReturnsBootstrapPayload(): void
    {
        $repository = new InMemoryJoinBootstrapRepository();
        $service = new JoinBootstrapService(
            $repository,
            new AvatarCatalog(),
            new PredictableSessionTokenGenerator('temporary-session-token'),
        );

        $result = $service->bootstrap('synergy-report-telemetry', [
            'displayName' => '  Pam  ',
            'avatar' => [
                // BEGIN AGENT CHANGE
                'id' => 'avatar-1',
                // END AGENT CHANGE
            ],
        ]);

        self::assertSame('temporary-session-token', $result->sessionToken);
        self::assertSame('Pam', $result->player->displayName);
        self::assertTrue($result->player->isHost);
        self::assertSame(1, $result->game->playerCount);
        self::assertCount(1, $result->joinedPlayers);
    }

    public function testItReturnsAnExistingPlayerWhenAGoodSessionTokenIsProvided(): void
    {
        $repository = new InMemoryJoinBootstrapRepository();
        $service = new JoinBootstrapService(
            $repository,
            new AvatarCatalog(),
            new PredictableSessionTokenGenerator('unused-token'),
        );

        $initial = $service->bootstrap('synergy-report-telemetry', [
            'displayName' => 'Pam',
            'avatar' => [
                // BEGIN AGENT CHANGE
                'id' => 'avatar-1',
                // END AGENT CHANGE
            ],
        ]);

        $reconnect = $service->bootstrap('synergy-report-telemetry', [
            'sessionToken' => $initial->sessionToken,
        ]);

        self::assertSame($initial->player->gamePlayerId, $reconnect->player->gamePlayerId);
        self::assertSame($initial->sessionToken, $reconnect->sessionToken);
    }

    public function testItRejectsDuplicateDisplayNamesWithinAGame(): void
    {
        $repository = new InMemoryJoinBootstrapRepository();
        $service = new JoinBootstrapService(
            $repository,
            new AvatarCatalog(),
            new PredictableSessionTokenGenerator('first-token'),
        );

        $service->bootstrap('synergy-report-telemetry', [
            'displayName' => 'Pam',
            'avatar' => [
                // BEGIN AGENT CHANGE
                'id' => 'avatar-1',
                // END AGENT CHANGE
            ],
        ]);

        $this->expectException(JoinBootstrapException::class);
        $this->expectExceptionMessage('Display names must be unique within the game.');

        $service->bootstrap('synergy-report-telemetry', [
            'displayName' => 'Pam',
            'avatar' => [
                // BEGIN AGENT CHANGE
                'id' => 'avatar-2',
                // END AGENT CHANGE
            ],
        ]);
    }

    public function testItRejectsDisplayNamesLongerThanTwentyFiveCharacters(): void
    {
        $service = new JoinBootstrapService(
            new InMemoryJoinBootstrapRepository(),
            new AvatarCatalog(),
            new PredictableSessionTokenGenerator('token'),
        );

        $this->expectException(JoinBootstrapException::class);
        $this->expectExceptionMessage('Display names must be 25 characters or fewer.');

        $service->bootstrap('synergy-report-telemetry', [
            'displayName' => 'This Display Name Is Way Too Long',
            'avatar' => [
                // BEGIN AGENT CHANGE
                'id' => 'avatar-1',
                // END AGENT CHANGE
            ],
        ]);
    }

    public function testItRejectsUnsupportedAvatarSelections(): void
    {
        $service = new JoinBootstrapService(
            new InMemoryJoinBootstrapRepository(),
            new AvatarCatalog(),
            new PredictableSessionTokenGenerator('token'),
        );

        $this->expectException(JoinBootstrapException::class);
        $this->expectExceptionMessage('Avatar selections must use supported Watercooler avatar options.');

        $service->bootstrap('synergy-report-telemetry', [
            'displayName' => 'Pam',
            'avatar' => [
                // BEGIN AGENT CHANGE
                'id' => 'unknown',
                // END AGENT CHANGE
            ],
        ]);
    }
}

final class InMemoryJoinBootstrapRepository implements JoinBootstrapRepository
{
    private GameSummary $game;

    /** @var array<int, JoinedPlayer> */
    private array $players = [];

    /** @var array<string, int> */
    private array $sessionTokenMap = [];

    private int $nextPlayerId = 1;
    private int $nextGamePlayerId = 1;

    public function __construct()
    {
        $this->game = new GameSummary(
            id: 1,
            slug: 'synergy-report-telemetry',
            status: 'lobby',
            phase: 'pre_join',
            playerCount: 0,
            createdAt: '2026-04-08 00:00:00',
        );
    }

    public function findGameBySlug(string $slug): ?GameSummary
    {
        return $slug === $this->game->slug ? $this->game : null;
    }

    public function displayNameExists(int $gameId, string $displayName): bool
    {
        foreach ($this->players as $player) {
            if ($player->displayName === $displayName) {
                return true;
            }
        }

        return false;
    }

    public function findPlayerBySessionToken(int $gameId, string $sessionTokenHash): ?JoinedPlayer
    {
        $gamePlayerId = $this->sessionTokenMap[$sessionTokenHash] ?? null;

        return $gamePlayerId === null ? null : $this->players[$gamePlayerId];
    }

    public function createJoinedPlayer(
        int $gameId,
        string $displayName,
        AvatarSelection $avatar,
        string $sessionTokenHash,
    ): JoinedPlayer {
        $player = new JoinedPlayer(
            gamePlayerId: $this->nextGamePlayerId++,
            playerId: $this->nextPlayerId++,
            displayName: $displayName,
            isHost: count($this->players) === 0,
            joinStatus: 'joined',
            avatar: $avatar,
        );

        $this->players[$player->gamePlayerId] = $player;
        $this->sessionTokenMap[$sessionTokenHash] = $player->gamePlayerId;
        $this->game = new GameSummary(
            id: $this->game->id,
            slug: $this->game->slug,
            status: 'lobby',
            phase: 'lobby',
            playerCount: count($this->players),
            createdAt: $this->game->createdAt,
        );

        return $player;
    }

    public function listPlayers(int $gameId): array
    {
        return array_values($this->players);
    }
}

final class PredictableSessionTokenGenerator implements SessionTokenGenerator
{
    public function __construct(
        private readonly string $token,
    ) {
    }

    public function generate(): string
    {
        return $this->token;
    }
}
