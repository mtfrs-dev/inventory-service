<?php

namespace App\Listeners;

use App\Events\UserProvisioned;
use Illuminate\Support\Facades\Log;

class LogUserProvisioned
{
    public function handle(UserProvisioned $event): void
    {
        Log::info('User provisioned from Auth Service token.', [
            'external_id' => $event->user->external_id,
        ]);
    }
}
