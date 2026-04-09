<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class PurchaseAdvantageService
{
    public function __construct(
        private readonly PurchaseAdvantageRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    public function purchase(string $slug, ?array $payload): PurchaseAdvantageResult
    {
        $game = $this->repository->findGameBySlug($slug);

        if ($game === null) {
            throw new PurchaseAdvantageException(404, 'game_not_found', 'No game exists for the provided slug.');
        }

        if ($game->status !== 'active' || $game->phase !== 'active') {
            throw new PurchaseAdvantageException(409, 'game_not_active', 'This Watercooler room is not currently accepting turn actions.');
        }

        $sessionToken = trim((string) ($payload['sessionToken'] ?? ''));
        if ($sessionToken === '') {
            throw new PurchaseAdvantageException(401, 'session_token_required', 'A valid temporary session token is required to purchase a Workplace Advantage.');
        }

        $actingPlayer = $this->repository->findPlayerBySessionToken($game->id, hash('sha256', $sessionToken));
        if ($actingPlayer === null) {
            throw new PurchaseAdvantageException(401, 'invalid_session_token', 'The provided temporary session token is invalid for this game.');
        }

        $state = $this->repository->loadState($game->id);
        if ($state->currentTurnGamePlayerId !== $actingPlayer->gamePlayerId) {
            throw new PurchaseAdvantageException(409, 'not_players_turn', 'Only the active player may purchase a Workplace Advantage right now.');
        }

        $playerState = $state->playerById($actingPlayer->gamePlayerId)
            ?? throw new \RuntimeException('The acting player could not be found in the active game state.');

        $source = trim((string) ($payload['source'] ?? ''));
        if (!in_array($source, ['market', 'reserved'], true)) {
            throw new PurchaseAdvantageException(422, 'invalid_purchase_source', 'Purchases must come from either the market or the player reserve.');
        }

        $selectedCard = null;
        $tier = null;
        $marketSlot = null;
        $cardCode = null;

        if ($source === 'market') {
            $tier = (int) ($payload['tier'] ?? 0);
            $marketSlot = (int) ($payload['marketSlot'] ?? 0);

            if (!in_array($tier, [1, 2, 3], true) || !in_array($marketSlot, [1, 2, 3, 4], true)) {
                throw new PurchaseAdvantageException(422, 'invalid_market_selection', 'Market purchases must identify a valid tier and market slot.');
            }

            foreach ($state->marketCardsByTier[$tier] ?? [] as $index => $card) {
                if ($index + 1 === $marketSlot) {
                    $selectedCard = $card;
                    break;
                }
            }

            if ($selectedCard === null) {
                throw new PurchaseAdvantageException(404, 'market_card_not_found', 'The selected market card is no longer available.');
            }
        } else {
            $cardCode = trim((string) ($payload['cardCode'] ?? ''));
            if ($cardCode === '') {
                throw new PurchaseAdvantageException(422, 'reserved_card_required', 'Reserved-card purchases must identify a reserved card code.');
            }

            foreach ($playerState->reservedCards as $card) {
                if ($card->code === $cardCode) {
                    $selectedCard = $card;
                    break;
                }
            }

            if ($selectedCard === null) {
                throw new PurchaseAdvantageException(404, 'reserved_card_not_found', 'The selected reserved card is not owned by this player.');
            }
        }

        $spentResources = $this->buildSpentResources($playerState, $selectedCard);

        $updatedState = $this->repository->applyPurchaseAdvantage(
            $game->id,
            $actingPlayer->gamePlayerId,
            $source,
            $tier,
            $marketSlot,
            $selectedCard->code,
            $spentResources,
            $state,
        );

        return new PurchaseAdvantageResult(
            game: $this->repository->findGameBySlug($slug)
                ?? throw new \RuntimeException('Updated game summary could not be reloaded.'),
            state: $updatedState,
        );
    }

    /**
     * @return array<string, int>
     */
    private function buildSpentResources(ActiveGamePlayer $player, PlayerCardView|CardSeedDefinition $card): array
    {
        $availableResources = [
            'coffee' => $player->resources->coffee,
            'spreadsheets' => $player->resources->spreadsheets,
            'budget' => $player->resources->budget,
            'connections' => $player->resources->connections,
            'time' => $player->resources->time,
        ];
        $spentResources = [
            'coffee' => 0,
            'spreadsheets' => 0,
            'budget' => 0,
            'connections' => 0,
            'time' => 0,
            'executiveFavor' => 0,
        ];

        $remainingShortfall = 0;

        foreach ($card->cost as $resource => $cost) {
            $discountedCost = max(0, $cost - ($player->permanentDiscounts[$resource] ?? 0));
            $spend = min($discountedCost, $availableResources[$resource]);
            $spentResources[$resource] = $spend;
            $remainingShortfall += $discountedCost - $spend;
        }

        if ($remainingShortfall > $player->resources->executiveFavor) {
            throw new PurchaseAdvantageException(
                409,
                'insufficient_resources',
                'The selected Workplace Advantage is not affordable with the player resources and Executive Favor currently available.',
            );
        }

        $spentResources['executiveFavor'] = $remainingShortfall;

        return $spentResources;
    }
}
