<?php

namespace App\Listeners;

use App\Events\TokenRevoked;
use Illuminate\Support\Facades\Log;

class LogTokenRevoked
{
    public function handle(TokenRevoked $event): void
    {
        Log::warning('Token revocation processed.', [
            'jti' => $event->jti,
            'subject' => $event->subject,
            'revoked_at' => $event->revokedAt,
        ]);
    }
}
