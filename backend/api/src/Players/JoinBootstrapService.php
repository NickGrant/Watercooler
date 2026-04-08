<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

final class JoinBootstrapService
{
    private const MAX_PLAYERS = 4;

    public function __construct(
        private readonly JoinBootstrapRepository $repository,
        private readonly AvatarCatalog $avatarCatalog,
        private readonly SessionTokenGenerator $sessionTokenGenerator,
    ) {
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    public function bootstrap(string $slug, ?array $payload): JoinBootstrapResult
    {
        $game = $this->repository->findGameBySlug($slug);

        if ($game === null) {
            throw new JoinBootstrapException(404, 'game_not_found', 'No game exists for the provided slug.');
        }

        $this->assertGameJoinable($game->status, $game->phase);

        $sessionToken = trim((string) ($payload['sessionToken'] ?? ''));
        if ($sessionToken !== '') {
            $player = $this->repository->findPlayerBySessionToken(
                $game->id,
                hash('sha256', $sessionToken),
            );

            if ($player === null) {
                throw new JoinBootstrapException(
                    401,
                    'invalid_session_token',
                    'The provided temporary session token is invalid for this game.',
                );
            }

            return new JoinBootstrapResult(
                game: $game,
                player: $player,
                sessionToken: $sessionToken,
                joinedPlayers: $this->repository->listPlayers($game->id),
            );
        }

        $displayName = trim((string) ($payload['displayName'] ?? ''));
        if ($displayName === '') {
            throw new JoinBootstrapException(
                422,
                'display_name_required',
                'Display name is required before joining a game.',
            );
        }

        if ($game->playerCount >= self::MAX_PLAYERS) {
            throw new JoinBootstrapException(
                409,
                'game_full',
                'This Watercooler room is already at maximum capacity.',
            );
        }

        if ($this->repository->displayNameExists($game->id, $displayName)) {
            throw new JoinBootstrapException(
                409,
                'display_name_taken',
                'Display names must be unique within the game.',
            );
        }

        $avatar = new AvatarSelection(
            body: trim((string) ($payload['avatar']['body'] ?? '')),
            face: trim((string) ($payload['avatar']['face'] ?? '')),
            hair: trim((string) ($payload['avatar']['hair'] ?? '')),
        );

        if (!$this->avatarCatalog->isValid($avatar)) {
            throw new JoinBootstrapException(
                422,
                'invalid_avatar',
                'Avatar selections must use supported Watercooler avatar options.',
            );
        }

        $sessionToken = $this->sessionTokenGenerator->generate();
        $player = $this->repository->createJoinedPlayer(
            gameId: $game->id,
            displayName: $displayName,
            avatar: $avatar,
            sessionTokenHash: hash('sha256', $sessionToken),
        );
        $updatedGame = $this->repository->findGameBySlug($slug)
            ?? throw new \RuntimeException('Joined game could not be reloaded from storage.');

        return new JoinBootstrapResult(
            game: $updatedGame,
            player: $player,
            sessionToken: $sessionToken,
            joinedPlayers: $this->repository->listPlayers($game->id),
        );
    }

    private function assertGameJoinable(string $status, string $phase): void
    {
        if ($status !== 'lobby' || !in_array($phase, ['pre_join', 'lobby'], true)) {
            throw new JoinBootstrapException(
                409,
                'game_not_joinable',
                'This Watercooler room is not accepting new players right now.',
            );
        }
    }
}
