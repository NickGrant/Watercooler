<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class ClaimProjectService
{
    public function __construct(
        private readonly ClaimProjectRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    public function claim(string $slug, ?array $payload): ClaimProjectResult
    {
        $game = $this->repository->findGameBySlug($slug);

        if ($game === null) {
            throw new ClaimProjectException(404, 'game_not_found', 'No game exists for the provided slug.');
        }

        if ($game->status !== 'active' || $game->phase !== 'active') {
            throw new ClaimProjectException(409, 'game_not_active', 'This Watercooler room is not currently accepting turn actions.');
        }

        $sessionToken = trim((string) ($payload['sessionToken'] ?? ''));
        if ($sessionToken === '') {
            throw new ClaimProjectException(401, 'session_token_required', 'A valid temporary session token is required to claim a project.');
        }

        $actingPlayer = $this->repository->findPlayerBySessionToken($game->id, hash('sha256', $sessionToken));
        if ($actingPlayer === null) {
            throw new ClaimProjectException(401, 'invalid_session_token', 'The provided temporary session token is invalid for this game.');
        }

        $state = $this->repository->loadState($game->id);
        if ($state->currentTurnGamePlayerId !== $actingPlayer->gamePlayerId) {
            throw new ClaimProjectException(409, 'not_players_turn', 'Only the active player may claim a project right now.');
        }

        $playerState = $state->playerById($actingPlayer->gamePlayerId)
            ?? throw new \RuntimeException('The acting player could not be found in the active game state.');

        if (count($playerState->reservedCards) >= 3) {
            throw new ClaimProjectException(409, 'reserve_limit_reached', 'A player may not hold more than three claimed projects.');
        }

        $source = trim((string) ($payload['source'] ?? ''));
        if (!in_array($source, ['market', 'deck'], true)) {
            throw new ClaimProjectException(422, 'invalid_claim_source', 'Claimed projects must come from either the market or the top of a tier deck.');
        }

        $tier = (int) ($payload['tier'] ?? 0);
        if (!in_array($tier, [1, 2, 3], true)) {
            throw new ClaimProjectException(422, 'invalid_claim_tier', 'Claimed projects must target a valid card tier.');
        }

        $marketSlot = $source === 'market' ? (int) ($payload['marketSlot'] ?? 0) : null;
        if ($source === 'market' && !in_array($marketSlot, [1, 2, 3, 4], true)) {
            throw new ClaimProjectException(422, 'invalid_market_slot', 'Market claims must identify a valid face-up card slot.');
        }

        $wouldGainExecutiveFavor = ($state->bank['executiveFavor'] ?? 0) > 0;
        if ($wouldGainExecutiveFavor && $playerState->resources->totalTokens() >= 10) {
            throw new ClaimProjectException(
                409,
                'resource_limit_exceeded',
                'Claiming a project while Executive Favor is available would exceed the 10-resource limit.',
            );
        }

        $updatedState = $this->repository->applyClaimProject(
            $game->id,
            $actingPlayer->gamePlayerId,
            $source,
            $tier,
            $marketSlot,
            $state,
        );

        return new ClaimProjectResult(
            game: $this->repository->findGameBySlug($slug)
                ?? throw new \RuntimeException('Updated game summary could not be reloaded.'),
            state: $updatedState,
        );
    }
}
