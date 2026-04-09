<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

use Watercooler\Api\Players\JoinBootstrapRepository;

final class LoadGameStateService
{
    public function __construct(
        private readonly JoinBootstrapRepository $lobbyRepository,
        private readonly GameStateProjectionRepository $projectionRepository,
    ) {
    }

    public function load(string $slug, string $sessionToken): LoadGameStateResult
    {
        $game = $this->lobbyRepository->findGameBySlug($slug);

        if ($game === null) {
            throw new LoadGameStateException(404, 'game_not_found', 'No game exists for the provided slug.');
        }

        $trimmedToken = trim($sessionToken);
        if ($trimmedToken === '') {
            throw new LoadGameStateException(401, 'session_token_required', 'A valid temporary session token is required to load game state.');
        }

        $player = $this->lobbyRepository->findPlayerBySessionToken($game->id, hash('sha256', $trimmedToken));
        if ($player === null) {
            throw new LoadGameStateException(401, 'invalid_session_token', 'The provided temporary session token is invalid for this game.');
        }

        if ($game->status === 'lobby' || in_array($game->phase, ['pre_join', 'lobby'], true)) {
            return new LoadGameStateResult(
                game: $game,
                player: $player,
                sessionToken: $trimmedToken,
                joinedPlayers: $this->lobbyRepository->listPlayers($game->id),
            );
        }

        return new LoadGameStateResult(
            game: $game,
            player: $player,
            sessionToken: $trimmedToken,
            state: $this->projectionRepository->loadState($game->id),
        );
    }
}
