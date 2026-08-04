<?php

namespace App\Listeners;

use App\Events\UserSynchronized;
use Illuminate\Support\Facades\Log;

class LogUserSynchronized
{
    public function handle(UserSynchronized $event): void
    {
        Log::info('User synchronized from Auth Service token.', [
            'external_id' => $event->user->external_id,
        ]);
    }
}
