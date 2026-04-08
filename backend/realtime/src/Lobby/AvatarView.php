<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Lobby;

final class AvatarView
{
    public function __construct(
        public readonly string $body,
        public readonly string $face,
        public readonly string $hair,
    ) {
    }

    /**
     * @return array{body: string, face: string, hair: string}
     */
    public function toArray(): array
    {
        return [
            'body' => $this->body,
            'face' => $this->face,
            'hair' => $this->hair,
        ];
    }
}
