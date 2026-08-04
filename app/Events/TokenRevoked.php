<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TokenRevoked
{
    use Dispatchable;

    public function __construct(
        public readonly ?string $jti,
        public readonly ?string $subject,
        public readonly int $revokedAt,
    ) {
    }
}
