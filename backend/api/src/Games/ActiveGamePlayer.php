<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class ActiveGamePlayer
{
    /**
     * @param array<string, int> $permanentDiscounts
     * @param list<PlayerCardView> $reservedCards
     * @param list<PlayerCardView> $purchasedCards
     * @param list<PlayerExecutiveView> $claimedExecutives
     */
    public function __construct(
        public readonly int $gamePlayerId,
        public readonly string $displayName,
        public readonly bool $isHost,
        public readonly string $joinStatus,
        public readonly int $seatOrder,
        public readonly int $officePrestige,
        public readonly PlayerResourceSet $resources,
        public readonly array $permanentDiscounts = [
            'coffee' => 0,
            'spreadsheets' => 0,
            'budget' => 0,
            'connections' => 0,
            'time' => 0,
        ],
        public readonly array $reservedCards = [],
        public readonly array $purchasedCards = [],
        public readonly array $claimedExecutives = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'gamePlayerId' => $this->gamePlayerId,
            'displayName' => $this->displayName,
            'isHost' => $this->isHost,
            'joinStatus' => $this->joinStatus,
            'seatOrder' => $this->seatOrder,
            'officePrestige' => $this->officePrestige,
            'resources' => $this->resources->toArray(),
            'permanentDiscounts' => $this->permanentDiscounts,
            'reservedCards' => array_map(
                static fn(PlayerCardView $card): array => $card->toArray(),
                $this->reservedCards,
            ),
            'purchasedCards' => array_map(
                static fn(PlayerCardView $card): array => $card->toArray(),
                $this->purchasedCards,
            ),
            'purchasedCardCount' => count($this->purchasedCards),
            'claimedExecutives' => array_map(
                static fn(PlayerExecutiveView $executive): array => $executive->toArray(),
                $this->claimedExecutives,
            ),
            'claimedExecutiveCount' => count($this->claimedExecutives),
        ];
    }
}
