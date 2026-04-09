<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class EndgameResolver
{
    public const TARGET_OFFICE_PRESTIGE = 15;

    public function shouldTriggerEndgame(ActiveGameState $state, int $actingGamePlayerId): bool
    {
        $actingPlayer = $state->playerById($actingGamePlayerId);

        return $actingPlayer !== null && $actingPlayer->officePrestige >= self::TARGET_OFFICE_PRESTIGE;
    }

    public function isLastSeat(ActiveGameState $state, int $actingGamePlayerId): bool
    {
        $players = $state->players;
        usort(
            $players,
            static fn(ActiveGamePlayer $left, ActiveGamePlayer $right): int => $left->seatOrder <=> $right->seatOrder,
        );

        return $players !== [] && $players[array_key_last($players)]->gamePlayerId === $actingGamePlayerId;
    }

    public function resolveWinner(ActiveGameState $state): EndgameWinner
    {
        $players = $state->players;
        usort(
            $players,
            static function (ActiveGamePlayer $left, ActiveGamePlayer $right): int {
                $prestigeComparison = $right->officePrestige <=> $left->officePrestige;
                if ($prestigeComparison !== 0) {
                    return $prestigeComparison;
                }

                $purchasedCardComparison = count($left->purchasedCards) <=> count($right->purchasedCards);
                if ($purchasedCardComparison !== 0) {
                    return $purchasedCardComparison;
                }

                return $left->seatOrder <=> $right->seatOrder;
            },
        );

        $winner = $players[0] ?? throw new \RuntimeException('The winner could not be resolved without any players.');
        $tiedGamePlayerIds = [];

        foreach ($players as $player) {
            if (
                $player->officePrestige === $winner->officePrestige
                && count($player->purchasedCards) === count($winner->purchasedCards)
            ) {
                $tiedGamePlayerIds[] = $player->gamePlayerId;
            }
        }

        return new EndgameWinner(
            winnerGamePlayerId: $winner->gamePlayerId,
            tiedGamePlayerIds: $tiedGamePlayerIds,
            winningPrestige: $winner->officePrestige,
            winningPurchasedCardCount: count($winner->purchasedCards),
        );
    }
}
