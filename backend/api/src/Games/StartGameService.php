<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class StartGameService
{
    public function __construct(
        private readonly StartGameRepository $repository,
        private readonly DeckShuffler $shuffler,
    ) {
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    public function start(string $slug, ?array $payload): StartGameResult
    {
        $game = $this->repository->findGameBySlug($slug);

        if ($game === null) {
            throw new StartGameException(404, 'game_not_found', 'No game exists for the provided slug.');
        }

        if ($game->status !== 'lobby' || !in_array($game->phase, ['pre_join', 'lobby'], true)) {
            throw new StartGameException(409, 'game_not_startable', 'This Watercooler room has already started.');
        }

        $sessionToken = trim((string) ($payload['sessionToken'] ?? ''));
        if ($sessionToken === '') {
            throw new StartGameException(401, 'session_token_required', 'A valid temporary session token is required to start the game.');
        }

        $requestingPlayer = $this->repository->findPlayerBySessionToken($game->id, hash('sha256', $sessionToken));
        if ($requestingPlayer === null) {
            throw new StartGameException(401, 'invalid_session_token', 'The provided temporary session token is invalid for this game.');
        }

        if (!$requestingPlayer->isHost) {
            throw new StartGameException(403, 'host_required', 'Only the host can start this Watercooler room.');
        }

        $players = $this->repository->listPlayers($game->id);
        if (count($players) < 2) {
            throw new StartGameException(409, 'not_enough_players', 'At least two employees must join before the game can start.');
        }

        if (count($players) > 4) {
            throw new StartGameException(409, 'too_many_players', 'Watercooler currently supports up to four players per game.');
        }

        if ($this->repository->listAvailableCards() === []) {
            throw new StartGameException(500, 'card_seed_missing', 'No seeded card content is available for game start orchestration.');
        }

        if ($this->repository->listAvailableExecutives() === []) {
            throw new StartGameException(500, 'executive_seed_missing', 'No seeded executive content is available for game start orchestration.');
        }

        $setup = $this->buildSetup($players);
        $updatedGame = $this->repository->persistStartedGame($game->id, $setup);

        return new StartGameResult(
            game: $updatedGame,
            state: new StartedGameState(
                players: $setup->players,
                currentTurnGamePlayerId: $setup->players[0]->gamePlayerId,
                bank: $setup->bank,
                marketCardsByTier: $setup->marketCardsByTier,
                executives: $setup->executives,
            ),
        );
    }

    /**
     * @param list<StartGamePlayer> $players
     */
    private function buildSetup(array $players): StartGameSetup
    {
        $orderedPlayers = array_map(
            static fn(StartGamePlayer $player, int $index): StartGamePlayer => new StartGamePlayer(
                gamePlayerId: $player->gamePlayerId,
                displayName: $player->displayName,
                isHost: $player->isHost,
                joinStatus: $player->joinStatus,
                seatOrder: $index + 1,
                officePrestige: 0,
            ),
            array_values($players),
            array_keys($players),
        );

        $cardsByTier = [1 => [], 2 => [], 3 => []];
        foreach ($this->repository->listAvailableCards() as $card) {
            $cardsByTier[$card->tier][] = $card;
        }

        $marketCardsByTier = [];
        $deckCardsByTier = [];

        foreach ($cardsByTier as $tier => $cards) {
            $shuffledCards = $this->shuffler->shuffle($cards);
            $marketCardsByTier[$tier] = array_slice($shuffledCards, 0, 4);
            $deckCardsByTier[$tier] = array_slice($shuffledCards, 4);
        }

        $executives = array_slice(
            $this->shuffler->shuffle($this->repository->listAvailableExecutives()),
            0,
            count($orderedPlayers) + 1,
        );

        return new StartGameSetup(
            players: $orderedPlayers,
            bank: $this->buildBank(count($orderedPlayers)),
            marketCardsByTier: $marketCardsByTier,
            deckCardsByTier: $deckCardsByTier,
            executives: $executives,
        );
    }

    /**
     * @return array<string, int>
     */
    private function buildBank(int $playerCount): array
    {
        $resourceCount = match ($playerCount) {
            2 => 4,
            3 => 5,
            4 => 7,
            default => throw new \InvalidArgumentException('Unsupported player count for Watercooler start state.'),
        };

        return [
            'coffee' => $resourceCount,
            'spreadsheets' => $resourceCount,
            'budget' => $resourceCount,
            'connections' => $resourceCount,
            'time' => $resourceCount,
            'executiveFavor' => 5,
        ];
    }
}
