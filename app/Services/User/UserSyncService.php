<?php

namespace App\Services\User;

use App\DTOs\TokenPayloadDTO;
use App\Events\UserProvisioned;
use App\Events\UserSynchronized;
use App\Models\User;

class UserSyncService
{
    public function syncFromToken(TokenPayloadDTO $token): User
    {
        $existing = User::where('external_id', $token->subject)->first();

        $user = User::updateOrCreate(
            ['external_id' => $token->subject],
            [
                'name' => $token->name,
                'email' => $token->email,
                'last_seen_roles' => $token->roles,
                'last_synced_at' => now(),
                'status' => 'active',
            ]
        );

        if ($existing === null) {
            event(new UserProvisioned($user));
        } else {
            event(new UserSynchronized($user));
        }

        return $user;
    }

    public function deactivate(string $externalId): void
    {
        User::where('external_id', $externalId)->update(['status' => 'inactive']);
    }
}
