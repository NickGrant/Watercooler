<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

final class AvatarCatalog
{
    private const BODY_OPTIONS = [
        'blazer',
        'hoodie',
        'cardigan',
        'polo',
        'power-suit',
        'body-1',
        'body-2',
        'body-3',
        'body-4',
        'body-5',
        'body-6',
        'body-7',
        'body-8',
        'body-9',
        'body-10',
    ];

    private const FACE_OPTIONS = [
        'corporate-neutral',
        'coffee-grin',
        'meeting-fatigue',
        'deadline-focus',
        'visionary-smirk',
        'face-1',
        'face-2',
        'face-3',
        'face-4',
        'face-5',
        'face-6',
        'face-7',
        'face-8',
        'face-9',
    ];

    private const HAIR_OPTIONS = [
        'side-part',
        'executive-swoop',
        'startup-mess',
        'weekend-buzz',
        'presentation-curl',
        'hair-1',
        'hair-2',
        'hair-3',
        'hair-4',
        'hair-5',
        'hair-6',
        'hair-7',
        'hair-8',
        'hair-9',
        'hair-10',
        'hair-11',
        'hair-12',
    ];

    public function isValid(AvatarSelection $selection): bool
    {
        return in_array($selection->body, self::BODY_OPTIONS, true)
            && in_array($selection->face, self::FACE_OPTIONS, true)
            && in_array($selection->hair, self::HAIR_OPTIONS, true);
    }
}
