<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class PlayerExecutiveView
{
    /**
     * @param array<string, int> $requirements
     */
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly int $officePrestige,
        public readonly array $requirements,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'officePrestige' => $this->officePrestige,
            'requirements' => $this->requirements,
        ];
    }
}
