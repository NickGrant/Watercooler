<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class TakeResourcesService
{
    /** @var list<string> */
    private const RESOURCE_KEYS = ['coffee', 'spreadsheets', 'budget', 'connections', 'time'];

    public function __construct(
        private readonly TakeResourcesRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    public function take(string $slug, ?array $payload): TakeResourcesResult
    {
        $game = $this->repository->findGameBySlug($slug);

        if ($game === null) {
            throw new TakeResourcesException(404, 'game_not_found', 'No game exists for the provided slug.');
        }

        if ($game->status !== 'active' || !in_array($game->phase, ['active', 'endgame'], true)) {
            throw new TakeResourcesException(409, 'game_not_active', 'This Watercooler room is not currently accepting turn actions.');
        }

        $sessionToken = trim((string) ($payload['sessionToken'] ?? ''));
        if ($sessionToken === '') {
            throw new TakeResourcesException(401, 'session_token_required', 'A valid temporary session token is required to take resources.');
        }

        $actingPlayer = $this->repository->findPlayerBySessionToken($game->id, hash('sha256', $sessionToken));
        if ($actingPlayer === null) {
            throw new TakeResourcesException(401, 'invalid_session_token', 'The provided temporary session token is invalid for this game.');
        }

        $state = $this->repository->loadState($game->id);
        if ($state->currentTurnGamePlayerId !== $actingPlayer->gamePlayerId) {
            throw new TakeResourcesException(409, 'not_players_turn', 'Only the active player may take resources right now.');
        }

        /** @var list<string> $resources */
        $resources = array_values(
            array_map(
                static fn(mixed $resource): string => trim((string) $resource),
                is_array($payload['resources'] ?? null) ? $payload['resources'] : [],
            ),
        );

        $this->assertSelectionIsLegal($resources, $state, $actingPlayer->gamePlayerId);

        $updatedState = $this->repository->applyTakeResources(
            $game->id,
            $actingPlayer->gamePlayerId,
            $resources,
            $state,
        );

        return new TakeResourcesResult(
            game: $this->repository->findGameBySlug($slug)
                ?? throw new \RuntimeException('Updated game summary could not be reloaded.'),
            state: $updatedState,
        );
    }

    /**
     * @param list<string> $resources
     */
    private function assertSelectionIsLegal(array $resources, ActiveGameState $state, int $actingGamePlayerId): void
    {
        if (!in_array(count($resources), [2, 3], true)) {
            throw new TakeResourcesException(
                422,
                'invalid_resource_selection_count',
                'Take-resource actions must select either two matching resources or three distinct resources.',
            );
        }

        foreach ($resources as $resource) {
            if (!in_array($resource, self::RESOURCE_KEYS, true)) {
                throw new TakeResourcesException(
                    422,
                    'invalid_resource_type',
                    'Take-resource actions may only use standard Watercooler resource colors.',
                );
            }
        }

        $actingPlayer = $state->playerById($actingGamePlayerId)
            ?? throw new \RuntimeException('The acting player could not be found in the active game state.');

        if ($actingPlayer->resources->totalTokens() + count($resources) > 10) {
            throw new TakeResourcesException(
                409,
                'resource_limit_exceeded',
                'A player may not hold more than ten resources after taking from the bank.',
            );
        }

        $resourceCounts = array_count_values($resources);

        if (count($resources) === 2) {
            if (count($resourceCounts) !== 1) {
                throw new TakeResourcesException(
                    422,
                    'double_take_requires_matching_resources',
                    'Taking two resources requires selecting the same resource twice.',
                );
            }

            $resource = $resources[0];
            if (($state->bank[$resource] ?? 0) < 4) {
                throw new TakeResourcesException(
                    409,
                    'insufficient_bank_for_double_take',
                    'Taking two matching resources requires at least four of that resource in the bank beforehand.',
                );
            }

            return;
        }

        if (count($resourceCounts) !== 3) {
            throw new TakeResourcesException(
                422,
                'three_take_requires_distinct_resources',
                'Taking three resources requires three different resource colors.',
            );
        }

        foreach (array_keys($resourceCounts) as $resource) {
            if (($state->bank[$resource] ?? 0) < 1) {
                throw new TakeResourcesException(
                    409,
                    'insufficient_bank_resources',
                    'The selected resources are not all currently available in the bank.',
                );
            }
        }
    }
}
