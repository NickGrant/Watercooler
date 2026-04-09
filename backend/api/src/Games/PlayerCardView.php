<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class PlayerCardView
{
    /**
     * @param array<string, int> $cost
     */
    public function __construct(
        public readonly string $code,
        public readonly int $tier,
        public readonly string $name,
        public readonly string $resourceDiscount,
        public readonly int $officePrestige,
        public readonly array $cost,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'tier' => $this->tier,
            'name' => $this->name,
            'resourceDiscount' => $this->resourceDiscount,
            'officePrestige' => $this->officePrestige,
            'cost' => $this->cost,
        ];
    }
}
