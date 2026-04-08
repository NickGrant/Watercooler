<?php

declare(strict_types=1);

namespace Watercooler\Api\Tests\Http\Handlers;

use PHPUnit\Framework\TestCase;
use Watercooler\Api\Games\GameSummary;
use Watercooler\Api\Http\Handlers\JoinBootstrapAction;
use Watercooler\Api\Http\Request;
use Watercooler\Api\Http\Routing\RouteMatch;
use Watercooler\Api\Players\AvatarCatalog;
use Watercooler\Api\Players\AvatarSelection;
use Watercooler\Api\Players\JoinBootstrapRepository;
use Watercooler\Api\Players\JoinBootstrapService;
use Watercooler\Api\Players\JoinedPlayer;
use Watercooler\Api\Players\SessionTokenGenerator;

final class JoinBootstrapActionTest extends TestCase
{
    public function testItReturnsJoinBootstrapPayload(): void
    {
        $action = new JoinBootstrapAction(
            new JoinBootstrapService(
                new HandlerJoinBootstrapRepository(),
                new AvatarCatalog(),
                new HandlerSessionTokenGenerator(),
            ),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/join-bootstrap',
                [],
                [],
                [
                    'displayName' => 'Pam',
                    'avatar' => [
                        'body' => 'blazer',
                        'face' => 'corporate-neutral',
                        'hair' => 'side-part',
                    ],
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(200, $response->statusCode);
        self::assertStringContainsString('"displayName": "Pam"', $response->body);
        self::assertStringContainsString('"token": "temporary-session-token"', $response->body);
    }

    public function testItReturnsValidationErrorsAsJson(): void
    {
        $action = new JoinBootstrapAction(
            new JoinBootstrapService(
                new HandlerJoinBootstrapRepository(),
                new AvatarCatalog(),
                new HandlerSessionTokenGenerator(),
            ),
        );

        $response = $action(
            new Request(
                'POST',
                '/api/games/synergy-report-telemetry/join-bootstrap',
                [],
                [],
                [
                    'displayName' => '   ',
                    'avatar' => [
                        'body' => 'blazer',
                        'face' => 'corporate-neutral',
                        'hair' => 'side-part',
                    ],
                ],
            ),
            new RouteMatch(static fn() => null, ['slug' => 'synergy-report-telemetry']),
        );

        self::assertSame(422, $response->statusCode);
        self::assertStringContainsString('display_name_required', $response->body);
    }
}

final class HandlerJoinBootstrapRepository implements JoinBootstrapRepository
{
    /** @var array<int, JoinedPlayer> */
    private array $players = [];

    private GameSummary $game;

    public function __construct()
    {
        $this->game = new GameSummary(1, 'synergy-report-telemetry', 'lobby', 'pre_join', 0, '2026-04-08 00:00:00');
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
        return null;
    }

    public function createJoinedPlayer(
        int $gameId,
        string $displayName,
        AvatarSelection $avatar,
        string $sessionTokenHash,
    ): JoinedPlayer {
        $player = new JoinedPlayer(
            gamePlayerId: 1,
            playerId: 1,
            displayName: $displayName,
            isHost: true,
            joinStatus: 'joined',
            avatar: $avatar,
        );

        $this->players[$player->gamePlayerId] = $player;
        $this->game = new GameSummary(1, 'synergy-report-telemetry', 'lobby', 'lobby', 1, '2026-04-08 00:00:00');

        return $player;
    }

    public function listPlayers(int $gameId): array
    {
        return array_values($this->players);
    }
}

final class HandlerSessionTokenGenerator implements SessionTokenGenerator
{
    public function generate(): string
    {
        return 'temporary-session-token';
    }
}
