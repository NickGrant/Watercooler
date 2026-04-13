<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

final class AvatarSelection
{
    public function __construct(
        public readonly string $id,
    ) {
    }

    /**
     * @return array{id: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
