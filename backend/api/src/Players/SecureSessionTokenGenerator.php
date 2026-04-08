<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

final class SecureSessionTokenGenerator implements SessionTokenGenerator
{
    public function generate(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    }
}
